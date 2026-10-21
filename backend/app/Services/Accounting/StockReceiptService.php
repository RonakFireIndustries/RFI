<?php

namespace App\Services\Accounting;

use App\Exceptions\StockQuantityException;
use App\Models\ProductStock;
use App\Models\StockReceipt;
use App\Models\StockReceiptItem;
use App\Models\TransactionLedger;
use App\Models\Voucher;
use App\Models\VoucherItem;
use App\Services\InventoryService;
use Illuminate\Support\Facades\DB;

/**
 * Goods receipts (and deliveries) against item vouchers.
 *
 * A purchase bill is keyed once, but the goods behind it arrive in as many
 * instalments as the supplier manages. This service records each instalment as
 * a `stock_receipts` row and keeps two things honest:
 *
 *   1. the running received / delivered total on each `voucher_items` line, so
 *      "how much is still pending" is a single column read, and
 *   2. `product_stock` + `transaction_ledger`, so the stock figures stay the
 *      single source of truth they already were.
 *
 * Everything is additive. Reversing a receipt does not delete the movement it
 * made; it writes the opposite `transaction_ledger` entry and marks the receipt
 * reversed, which is what an auditor expects to see.
 */
class StockReceiptService
{
    public function __construct(protected InventoryService $inventory)
    {
    }

    /**
     * The receipt fields to eager-load for the API.
     *
     * @return array<int, string>
     */
    public function relations(): array
    {
        return [
            'items.product:id,name,sku,unit_id',
            'party:id,name',
            'receiver:id,name',
            'reverser:id,name',
            'locationable:id,name',
        ];
    }

    /**
     * Record a delivery against one item voucher.
     *
     * `$data['items']` is `[{voucher_item_id, quantity, batch_no?}]`. Zero
     * quantities are skipped, so a warehouse can leave untouched lines in the
     * payload. The whole thing is rejected up-front if any line over-receives
     * or has no product attached, so a bad row cannot leave half a receipt
     * behind.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(Voucher $voucher, array $data, int $userId): StockReceipt
    {
        // Notes move their stock whole, at save. Letting a partial receipt be
        // keyed against one would double-count the return.
        if (NoteStockService::handles($voucher->voucher_type)) {
            throw new StockQuantityException(
                'A credit or debit note moves its stock when it is saved; there is nothing to receive.'
            );
        }

        $direction = $voucher->stockDirection();
        if ($direction === null) {
            throw new StockQuantityException('This voucher type does not move stock, so nothing can be received against it.');
        }

        $outbound = $direction === 'out';
        $lines = $this->planLines($voucher, $data['items'] ?? [], $outbound);

        if (empty($lines)) {
            throw new StockQuantityException('Enter a quantity for at least one line.');
        }

        [$locationType, $locationId] = $this->resolveLocation($data, $outbound);

        return DB::transaction(function () use ($voucher, $data, $userId, $outbound, $lines, $locationType, $locationId) {
            $receipt = StockReceipt::create([
                'receipt_no' => $this->nextNumber($outbound ? 'DLV' : 'GRN'),
                'voucher_type' => $voucher->voucher_type,
                'voucher_id' => $voucher->id,
                'receipt_date' => $data['receipt_date'],
                'party_ledger_id' => $voucher->party_ledger_id,
                'location_type' => $locationType,
                'location_id' => $locationId,
                'status' => 'active',
                'notes' => $data['notes'] ?? null,
                'supplier_bill_no' => $data['supplier_bill_no'] ?? null,
                'supplier_bill_date' => $data['supplier_bill_date'] ?? null,
                'attachment_path' => $data['attachment_path'] ?? null,
                'received_by' => $userId,
            ]);

            foreach ($lines as $line) {
                /** @var VoucherItem $item */
                $item = $line['item'];

                // The movement is recorded before the counter moves, so a stock
                // failure (an outbound delivery that would go negative) rolls
                // the whole receipt back and the counter can never overstate.
                $movement = $this->inventory->recordTransaction(
                    productId: $item->product_id,
                    locationType: $locationType,
                    locationId: $locationId,
                    transactionType: $outbound ? 'sales' : 'purchase',
                    quantity: $line['quantity'],
                    unitPrice: (float) $item->rate,
                    referenceType: $outbound ? 'delivery_note' : 'goods_receipt',
                    referenceId: $receipt->id,
                    notes: $this->movementNote($receipt, $voucher, $item),
                );

                $column = $item->movementColumn();

                StockReceiptItem::create([
                    'stock_receipt_id' => $receipt->id,
                    'voucher_item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'quantity' => $line['quantity'],
                    'quantity_before' => $item->moved_quantity,
                    'quantity_after' => round($item->moved_quantity + $line['quantity'], 3),
                    'transaction_ledger_id' => $movement->id,
                    'batch_no' => $line['batch_no'] ?? null,
                ]);

                $item->update([$column => round((float) $item->{$column} + $line['quantity'], 3)]);
            }

            return $receipt->fresh()->load($this->relations());
        });
    }

    /**
     * Undo a receipt without erasing it: the stock movement is written back,
     * the item counters come down, and the row is stamped `reversed`.
     */
    public function reverse(StockReceipt $receipt, int $userId, ?string $reason = null): StockReceipt
    {
        if ($receipt->isReversed()) {
            throw new StockQuantityException("Receipt {$receipt->receipt_no} has already been reversed.");
        }

        $outbound = $receipt->voucher_type === 'sales';

        return DB::transaction(function () use ($receipt, $userId, $reason, $outbound) {
            foreach ($receipt->items as $line) {
                $item = $line->voucherItem()->first();

                if ($item && $line->product_id) {
                    $this->inventory->recordTransaction(
                        productId: $line->product_id,
                        locationType: $receipt->location_type,
                        locationId: (int) $receipt->location_id,
                        transactionType: $outbound ? 'sales_return' : 'adjustment',
                        quantity: (float) $line->quantity,
                        unitPrice: (float) $item->rate,
                        referenceType: 'receipt_reversal',
                        referenceId: $receipt->id,
                        notes: "Reversal of {$receipt->receipt_no}"
                            . ($outbound ? ' (goods returned)' : ' (receipt cancelled)'),
                    );

                    $column = $item->movementColumn();
                    $item->update([
                        $column => round(max(0, (float) $item->{$column} - (float) $line->quantity), 3),
                    ]);
                }

                // The movement is gone, so the pointer to it is cleared; the
                // receipt line itself stays for the audit trail.
                $line->update(['transaction_ledger_id' => null]);
            }

            $receipt->update([
                'status' => 'reversed',
                'reversed_by' => $userId,
                'reversed_at' => now(),
                'reversal_reason' => $reason,
            ]);

            return $receipt->fresh()->load($this->relations());
        });
    }

    /**
     * The whole receipt picture for one voucher: what was ordered, what has
     * come in, what is still outstanding, and the dated history behind it.
     *
     * @return array<string, mixed>
     */
    public function summary(Voucher $voucher): array
    {
        $voucher->loadMissing([
            'items.product:id,name,sku',
            'items.receivedLines.receipt:id,receipt_no,receipt_date,status,location_type,location_id',
            'party:id,name',
            'activeStockReceipts',
        ]);

        $outbound = $voucher->stockDirection() === 'out';

        $lines = $voucher->items->map(fn (VoucherItem $item) => [
            'voucher_item_id' => $item->id,
            'line_no' => $item->line_no,
            'product_id' => $item->product_id,
            'product_name' => $item->product?->name,
            'sku' => $item->product?->sku,
            'item_description' => $item->item_description,
            'unit' => $item->unit,
            'quantity' => (float) $item->quantity,
            'moved_quantity' => $item->moved_quantity,
            'pending_quantity' => $item->pending_quantity,
            'tracks_stock' => $item->product_id !== null,
            'receipts' => $item->receivedLines
                ->map(fn (StockReceiptItem $line) => [
                    'stock_receipt_id' => $line->stock_receipt_id,
                    'receipt_no' => $line->receipt?->receipt_no,
                    'receipt_date' => $line->receipt?->receipt_date?->toDateString(),
                    'status' => $line->receipt?->status,
                    'quantity' => (float) $line->quantity,
                    'quantity_before' => (float) $line->quantity_before,
                    'quantity_after' => (float) $line->quantity_after,
                    'batch_no' => $line->batch_no,
                ])->values(),
        ])->values();

        [$status, $ordered, $moved] = $this->deriveStatus($voucher);

        return [
            'direction' => $outbound ? 'out' : 'in',
            'tracks_stock' => $voucher->tracksStock(),
            'status' => $status,
            'status_label' => $this->statusLabel($status, $outbound),
            'ordered_quantity' => $ordered,
            'moved_quantity' => $moved,
            'pending_quantity' => round(max(0, $ordered - $moved), 3),
            'readiness' => $this->readiness($voucher, $outbound),
            'lines' => $lines,
            'receipts' => $voucher->stockReceipts()
                ->with($this->relations())
                ->get()
                ->map(fn (StockReceipt $receipt) => $this->receiptPayload($receipt))
                ->values(),
        ];
    }

    /**
     * Per-line warehouse facts the voucher form shows while the bill is being
     * keyed: what is on hand at each site, and (for a sale) whether the line
     * would take stock negative.
     *
     * @return array<string, mixed>
     */
    public function availability(Voucher $voucher): array
    {
        $voucher->loadMissing('items');

        $productIds = $voucher->items->pluck('product_id')->filter()->unique()->values();
        $outbound = $voucher->stockDirection() === 'out';

        $rows = ProductStock::query()
            ->whereIn('product_id', $productIds)
            ->with(['locationable' => fn ($q) => $q->select('id', 'name')])
            ->get();

        $byProduct = [];
        foreach ($rows as $row) {
            $byProduct[$row->product_id][] = [
                'location_type' => $row->location_type,
                'location_id' => $row->location_id,
                'location_name' => $row->locationable?->name,
                'quantity' => (float) $row->quantity,
                'available_quantity' => (float) $row->available_quantity,
                'reserved_quantity' => (float) $row->reserved_quantity,
            ];
        }

        $lines = $voucher->items->map(function (VoucherItem $item) use ($byProduct, $outbound) {
            $locations = $item->product_id ? ($byProduct[$item->product_id] ?? []) : [];
            $onHand = round(array_sum(array_column($locations, 'available_quantity')), 3);
            $requested = (float) $item->quantity;

            return [
                'voucher_item_id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product?->name,
                'tracked' => $item->product_id !== null,
                'requested_quantity' => $requested,
                'on_hand_quantity' => $item->product_id ? $onHand : null,
                // Only a sale can be short: a purchase is what creates stock.
                'shortfall_quantity' => $outbound && $item->product_id && $onHand < $requested
                    ? round($requested - $onHand, 3)
                    : 0,
                'locations' => $locations,
            ];
        })->values();

        return [
            'direction' => $outbound ? 'out' : 'in',
            'mode' => $outbound ? 'delivery' : 'receipt',
            'lines' => $lines,
        ];
    }

    /**
     * The receipt register, filtered by the usual document fields.
     */
    public function register(array $filters): array
    {
        $query = StockReceipt::query()
            ->with($this->relations())
            ->withSum('items as quantity_total', 'quantity');

        if (! empty($filters['voucher_type'])) {
            $query->where('voucher_type', $filters['voucher_type']);
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['from'])) {
            $query->whereDate('receipt_date', '>=', $filters['from']);
        }
        if (! empty($filters['to'])) {
            $query->whereDate('receipt_date', '<=', $filters['to']);
        }
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('receipt_no', 'like', "%{$search}%")
                    ->orWhere('supplier_bill_no', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('voucher', fn ($v) => $v->where('voucher_no', 'like', "%{$search}%")
                        ->orWhere('reference_no', 'like', "%{$search}%"))
                    ->orWhereHas('party', fn ($p) => $p->where('name', 'like', "%{$search}%"));
            });
        }

        return $query->orderByDesc('receipt_date')->orderByDesc('id')->get()
            ->map(fn (StockReceipt $receipt) => $this->receiptPayload($receipt))
            ->values()
            ->all();
    }

    /**
     * Receipt breakdown: what actually moved, per product, for a period.
     *
     * The receipt register answers "what was booked, when"; this report answers
     * the question the storekeeper and the accountant both ask — *how much of
     * each product came in and went out, at what value, and how much of the
     * period's billing is still outstanding*.
     *
     * Figures come from the active receipt lines only: a reversed receipt is
     * excluded from every total, because its stock movement was undone. The
     * period is by `receipt_date` (when the goods moved), not by the voucher
     * date — a March bill received in April belongs to April's movement.
     *
     * @param  array<string, mixed>  $filters  from, to, voucher_type, location_id, party_ledger_id
     * @return array<string, mixed>
     */
    public function breakdown(array $filters): array
    {
        [$from, $to] = $this->resolveReportPeriod($filters);

        $query = StockReceipt::query()
            ->where('status', 'active')
            ->with(['items.product:id,name,sku', 'items.voucherItem:id,rate', 'voucher:id,voucher_no,voucher_type', 'party:id,name', 'locationable:id,name']);

        if (! empty($filters['voucher_type'])) {
            $query->where('voucher_type', $filters['voucher_type']);
        }
        if (! empty($filters['party_ledger_id'])) {
            $query->where('party_ledger_id', $filters['party_ledger_id']);
        }
        if (! empty($filters['location_id'])) {
            $query->where('location_id', $filters['location_id']);
        }
        if ($from) {
            $query->whereDate('receipt_date', '>=', $from);
        }
        if ($to) {
            $query->whereDate('receipt_date', '<=', $to);
        }

        $receipts = $query->orderBy('receipt_date')->orderBy('id')->get();

        // One bucket per product, split by direction so a product that is both
        // bought and sold never mixes its in and out figures.
        $products = [];
        $parties = [];
        $receiptRows = [];
        $productReceipts = [];   // product id => [receipt id => true]
        $partyReceipts = [];     // party id   => [receipt id => true]
        $totals = [
            'receipts' => 0, 'inbound_quantity' => 0.0, 'outbound_quantity' => 0.0,
            'inbound_value' => 0.0, 'outbound_value' => 0.0,
        ];

        foreach ($receipts as $receipt) {
            $outbound = $receipt->voucher_type === 'sales';
            $totals['receipts']++;
            $totals[$outbound ? 'outbound_quantity' : 'inbound_quantity'] += (float) $receipt->items->sum('quantity');

            $lineSummary = [];

            foreach ($receipt->items as $line) {
                $quantity = (float) $line->quantity;
                $value = round($quantity * (float) ($line->voucherItem?->rate ?? 0), 2);
                $productId = $line->product_id ?? 0;
                $name = $line->product?->name ?? 'Unknown product';

                $bucket = $products[$productId] ?? [
                    'product_id' => $line->product_id,
                    'product_name' => $name,
                    'sku' => $line->product?->sku,
                    'inbound_quantity' => 0.0, 'outbound_quantity' => 0.0,
                    'inbound_value' => 0.0, 'outbound_value' => 0.0,
                    'receipt_count' => 0,
                ];
                $key = $outbound ? 'outbound' : 'inbound';
                $bucket["{$key}_quantity"] += $quantity;
                $bucket["{$key}_value"] += $value;
                $products[$productId] = $bucket;
                $productReceipts[$productId][$receipt->id] = true;

                $totals[$outbound ? 'outbound_value' : 'inbound_value'] += $value;

                $partyId = $receipt->party_ledger_id ?? 0;
                if ($partyId) {
                    $parties[$partyId] ??= [
                        'party_ledger_id' => $receipt->party_ledger_id,
                        'party_name' => $receipt->party?->name ?? 'Unknown party',
                        'inbound_quantity' => 0.0, 'outbound_quantity' => 0.0,
                        'receipt_count' => 0,
                    ];
                    $parties[$partyId]["{$key}_quantity"] += $quantity;
                    $partyReceipts[$partyId][$receipt->id] = true;
                }

                $lineSummary[] = [
                    'product_id' => $line->product_id,
                    'product_name' => $name,
                    'quantity' => $quantity,
                    'value' => $value,
                ];
            }

            $receiptRows[] = [
                'receipt_id' => $receipt->id,
                'receipt_no' => $receipt->receipt_no,
                'receipt_date' => $this->dateOnly($receipt->receipt_date),
                'direction' => $outbound ? 'out' : 'in',
                'voucher_id' => $receipt->voucher_id,
                'voucher_no' => $receipt->voucher?->voucher_no,
                'voucher_type' => $receipt->voucher_type,
                'party_name' => $receipt->party?->name,
                'location_name' => $receipt->locationable?->name,
                'supplier_bill_no' => $receipt->supplier_bill_no,
                'quantity_total' => (float) $receipt->items->sum('quantity'),
                'lines' => $lineSummary,
            ];
        }

        // "Across N deliveries" — distinct documents, not lines, so a receipt
        // with two lines of the same product still reads as one delivery.
        foreach ($products as $pid => $bucket) {
            $products[$pid]['receipt_count'] = count($productReceipts[$pid] ?? []);
        }
        foreach ($parties as $pid => $bucket) {
            $parties[$pid]['receipt_count'] = count($partyReceipts[$pid] ?? []);
        }

        $productRows = array_values($products);
        usort($productRows, fn ($a, $b) => ($b['inbound_quantity'] + $b['outbound_quantity']) <=> ($a['inbound_quantity'] + $a['outbound_quantity']));

        $partyRows = array_values($parties);
        usort($partyRows, fn ($a, $b) => ($b['inbound_quantity'] + $b['outbound_quantity']) <=> ($a['inbound_quantity'] + $a['outbound_quantity']));

        foreach ($totals as $key => $value) {
            $totals[$key] = is_float($value) ? round($value, 3) : $value;
        }
        $totals['inbound_value'] = round($totals['inbound_value'], 2);
        $totals['outbound_value'] = round($totals['outbound_value'], 2);
        $totals['net_quantity'] = round($totals['inbound_quantity'] - $totals['outbound_quantity'], 3);

        return [
            'period' => ['from' => $from, 'to' => $to],
            'totals' => $totals,
            'products' => $productRows,
            'parties' => $partyRows,
            'receipts' => $receiptRows,
            'outstanding' => $this->outstandingByVoucher($filters, $from, $to),
        ];
    }

    /**
     * Billed-but-not-yet-moved quantity for the period's documents.
     *
     * A receipt report that only counted what arrived would quietly hide the
     * shortfall; this is the "still owed" half of the same question, grouped by
     * voucher so the reader can chase the right supplier.
     *
     * @param  array<string, mixed>  $filters
     * @return array<int, array<string, mixed>>
     */
    protected function outstandingByVoucher(array $filters, ?string $from, ?string $to): array
    {
        $query = Voucher::query()
            ->where('status', 'posted')
            ->whereHas('items', fn ($q) => $q->whereNotNull('product_id'))
            ->with(['items', 'party:id,name']);

        if (! empty($filters['voucher_type'])) {
            $query->where('voucher_type', $filters['voucher_type']);
        } elseif ($types = $this->stockVoucherTypes()) {
            $query->whereIn('voucher_type', $types);
        } else {
            return [];
        }

        if (! empty($filters['party_ledger_id'])) {
            $query->where('party_ledger_id', $filters['party_ledger_id']);
        }
        if ($from) {
            $query->whereDate('voucher_date', '>=', $from);
        }
        if ($to) {
            $query->whereDate('voucher_date', '<=', $to);
        }

        return $query->orderBy('voucher_date')->orderBy('id')->get()
            ->map(function (Voucher $voucher) {
                $tracked = $voucher->items->whereNotNull('product_id');
                $ordered = round((float) $tracked->sum('quantity'), 3);
                // Read the counter straight off the parent's direction rather
                // than through VoucherItem::moved_quantity, which would look the
                // voucher up again for every line.
                $column = $voucher->stockDirection() === 'out' ? 'delivered_quantity' : 'received_quantity';
                $moved = round((float) $tracked->sum(fn (VoucherItem $item) => (float) $item->{$column}), 3);
                $pending = round(max(0, $ordered - $moved), 3);

                return [
                    'voucher_id' => $voucher->id,
                    'voucher_no' => $voucher->voucher_no,
                    'voucher_type' => $voucher->voucher_type,
                    'voucher_date' => $this->dateOnly($voucher->voucher_date),
                    'direction' => $voucher->stockDirection() === 'out' ? 'out' : 'in',
                    'party_name' => $voucher->party?->name,
                    'ordered_quantity' => $ordered,
                    'moved_quantity' => $moved,
                    'pending_quantity' => $pending,
                    'status' => match (true) {
                        $moved <= 0.0005 => 'pending',
                        $pending <= 0.0005 => 'complete',
                        default => 'partial',
                    },
                ];
            })
            ->filter(fn (array $row) => $row['pending_quantity'] > 0.0005)
            ->values()
            ->all();
    }

    /**
     * Voucher types that move stock, from config/accounting.php.
     *
     * @return array<int, string>
     */
    protected function stockVoucherTypes(): array
    {
        return collect(config('accounting.voucher_types', []))
            ->filter(fn (array $type) => ! empty($type['stock']))
            ->keys()
            ->values()
            ->all();
    }

    /**
     * Resolve the report window. `to` defaults to today; `from` to the start of
     * the active financial year, matching the finance report pack.
     *
     * @param  array<string, mixed>  $filters
     * @return array{0: ?string, 1: ?string}
     */
    protected function resolveReportPeriod(array $filters): array
    {
        $to = $this->dateOnly($filters['to'] ?? null) ?? now()->toDateString();
        $from = $this->dateOnly($filters['from'] ?? null);

        if ($from === null) {
            $from = $this->dateOnly(
                \App\Models\FinancialYear::where('is_active', true)->orderByDesc('start_date')->value('start_date')
            );
        }

        if ($from !== null && $from > $to) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'from' => 'The from date must be on or before the to date.',
            ]);
        }

        return [$from, $to];
    }

    /** Normalise casts, strings and Carbon values to Y-m-d. */
    protected function dateOnly(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_object($value) && method_exists($value, 'toDateString')) {
            return $value->toDateString();
        }

        return substr((string) $value, 0, 10);
    }

    /**
     * Recompute a voucher's counters from its active receipt lines.
     *
     * The counters are maintained incrementally on the write path; this is the
     * repair tool for when a receipt was reversed out of band or a voucher line
     * was edited after goods had arrived. Returns the rows it corrected.
     *
     * @return array<int, array<string, float|int>>
     */
    public function reconcile(Voucher $voucher): array
    {
        $voucher->loadMissing('items');
        $corrected = [];

        DB::transaction(function () use ($voucher, &$corrected) {
            foreach ($voucher->items as $item) {
                $received = (float) StockReceiptItem::query()
                    ->where('voucher_item_id', $item->id)
                    ->whereHas('receipt', fn ($q) => $q->where('status', 'active'))
                    ->sum('quantity');

                if (abs($received - (float) $item->moved_quantity) < 0.0005) {
                    continue;
                }

                $corrected[] = [
                    'voucher_item_id' => $item->id,
                    'was' => $item->moved_quantity,
                    'now' => round($received, 3),
                ];

                // Both counters are rewritten: a line can only be on one side
                // of the fence, so the other one is stale by definition.
                $item->update([
                    'received_quantity' => $item->isOutbound() ? 0 : round($received, 3),
                    'delivered_quantity' => $item->isOutbound() ? round($received, 3) : 0,
                ]);
            }
        });

        return $corrected;
    }

    /* ------------------------------ internals ----------------------------- */

    /**
     * Validate the requested lines against the voucher and the outstanding
     * quantities, returning them with their voucher item attached.
     *
     * @param  array<int, array<string, mixed>>  $requested
     * @return array<int, array{item: VoucherItem, quantity: float, batch_no: ?string}>
     */
    protected function planLines(Voucher $voucher, array $requested, bool $outbound): array
    {
        $voucher->loadMissing('items');

        $byId = $requested === [] ? collect() : collect($requested)
            ->keyBy(fn ($row) => (int) ($row['voucher_item_id'] ?? 0));

        $planned = [];
        $seen = [];

        foreach ($byId as $voucherItemId => $row) {
            $quantity = round((float) ($row['quantity'] ?? 0), 3);

            if ($quantity <= 0) {
                continue;
            }

            /** @var VoucherItem|null $item */
            $item = $voucher->items->firstWhere('id', (int) $voucherItemId);

            if (! $item) {
                throw new StockQuantityException('That line does not belong to this document.');
            }

            if (($seen[$item->id] ?? false)) {
                throw new StockQuantityException("{$item->item_description} appears twice in the same receipt. Combine the lines.");
            }
            $seen[$item->id] = true;

            if (! $item->product_id) {
                throw new StockQuantityException(
                    "\"{$item->item_description}\" is a free-text line with no product, so it cannot move stock."
                );
            }

            $pending = $item->pending_quantity;
            if ($quantity > $pending + 0.0005) {
                throw new StockQuantityException(sprintf(
                    'Only %s of %s %s still pending on %s.',
                    $this->qty($pending),
                    $this->qty($item->quantity),
                    $item->item_description,
                    $outbound ? 'this sale' : 'this purchase'
                ));
            }

            $planned[] = [
                'item' => $item,
                'quantity' => $quantity,
                'batch_no' => $row['batch_no'] ?? null,
            ];
        }

        return $planned;
    }

    /**
     * Where the goods went (receipt) or came from (delivery). Omitted means the
     * only site there is; a receipt with no location is refused rather than
     * silently creating an unlocatable stock row.
     *
     * @return array{0: string, 1: int}
     */
    protected function resolveLocation(array $data, bool $outbound): array
    {
        $type = $data['location_type'] ?? null;
        $id = $data['location_id'] ?? null;

        if (! $type || ! $id) {
            $default = \App\Models\Site::query()->orderBy('id')->value('id');

            if (! $default) {
                throw new StockQuantityException('Create a site first — stock is tracked per site.');
            }

            return [\App\Models\Site::class, (int) $default];
        }

        if (str_contains((string) $type, '\\')) {
            $model = $type;
        } else {
            $model = match ($type) {
                'site' => \App\Models\Site::class,
                default => throw new StockQuantityException("Unknown stock location type \"{$type}\"."),
            };
        }

        if (! $model::whereKey($id)->exists()) {
            throw new StockQuantityException('The selected stock location no longer exists.');
        }

        return [$model, (int) $id];
    }

    protected function movementNote(StockReceipt $receipt, Voucher $voucher, VoucherItem $item): string
    {
        $kind = $receipt->voucher_type === 'sales' ? 'Delivery' : 'Goods receipt';

        return "{$kind} {$receipt->receipt_no} against {$voucher->voucher_no} — {$item->item_description}";
    }

    /**
     * `not_tracked` when the document has no product lines at all, otherwise
     * pending / partial / complete from the counters.
     *
     * @return array{0: string, 1: float, 2: float}
     */
    protected function deriveStatus(Voucher $voucher): array
    {
        $tolerance = (float) config('accounting.stock_tracking.quantity_tolerance', 0.001);

        $tracked = $voucher->items->whereNotNull('product_id');

        if ($tracked->isEmpty()) {
            return ['not_tracked', 0.0, 0.0];
        }

        $ordered = round((float) $tracked->sum('quantity'), 3);
        $moved = round((float) $tracked->sum('moved_quantity'), 3);

        $status = match (true) {
            $moved <= $tolerance => 'pending',
            $moved >= $ordered - $tolerance => 'complete',
            default => 'partial',
        };

        return [$status, $ordered, $moved];
    }

    protected function statusLabel(string $status, bool $outbound): string
    {
        $inbound = [
            'not_tracked' => 'No stock to receive',
            'pending' => 'Not received',
            'partial' => 'Partially received',
            'complete' => 'Fully received',
        ];

        $outboundLabels = [
            'not_tracked' => 'No stock to deliver',
            'pending' => 'Not delivered',
            'partial' => 'Partially delivered',
            'complete' => 'Fully delivered',
        ];

        return ($outbound ? $outboundLabels : $inbound)[$status] ?? $status;
    }

    /**
     * Whether the books and the warehouse can be made to agree — surfaced so the
     * UI can say *why* a bill is not yet fully received instead of just that it
     * is not.
     *
     * @return array<int, string>
     */
    protected function readiness(Voucher $voucher, bool $outbound): array
    {
        $notes = [];

        $untracked = $voucher->items->whereNull('product_id')->count();
        if ($untracked > 0) {
            $notes[] = "{$untracked} line(s) have no product, so they will never be received.";
        }

        if (! $voucher->tracksStock()) {
            $notes[] = 'This voucher type does not post stock movements.';
        }

        foreach ($voucher->items as $item) {
            if ($item->moved_quantity > (float) $item->quantity + 0.0005) {
                $notes[] = "{$item->item_description} has more received than billed — check the receipt history.";
                break;
            }
        }

        if ($outbound && ! $notes) {
            $notes[] = 'Saving is allowed even when stock is short; the delivery is what removes it.';
        }

        return $notes;
    }

    /**
     * @return array<string, mixed>
     */
    protected function receiptPayload(StockReceipt $receipt): array
    {
        return [
            'id' => $receipt->id,
            'receipt_no' => $receipt->receipt_no,
            'voucher_id' => $receipt->voucher_id,
            'voucher_no' => $receipt->voucher?->voucher_no,
            'voucher_type' => $receipt->voucher_type,
            'receipt_date' => $receipt->receipt_date?->toDateString(),
            'status' => $receipt->status,
            'source' => $receipt->voucher_type === 'sales' ? 'delivery' : 'goods_receipt',
            'party_ledger_id' => $receipt->party_ledger_id,
            'party_name' => $receipt->party?->name,
            'location_type' => $receipt->location_type,
            'location_id' => $receipt->location_id,
            'location_name' => $receipt->locationable?->name,
            'supplier_bill_no' => $receipt->supplier_bill_no,
            'supplier_bill_date' => $receipt->supplier_bill_date?->toDateString(),
            'attachment_path' => $receipt->attachment_path,
            'notes' => $receipt->notes,
            'reversal_reason' => $receipt->reversal_reason,
            'reversed_at' => $receipt->reversed_at?->toIso8601String(),
            'received_by' => $receipt->received_by,
            'received_by_name' => $receipt->receiver?->name,
            'reversed_by_name' => $receipt->reverser?->name,
            'quantity_total' => (float) ($receipt->quantity_total ?? $receipt->items->sum('quantity')),
            'items' => $receipt->items->map(fn (StockReceiptItem $line) => [
                'id' => $line->id,
                'voucher_item_id' => $line->voucher_item_id,
                'product_id' => $line->product_id,
                'product_name' => $line->product?->name,
                'quantity' => (float) $line->quantity,
                'quantity_before' => (float) $line->quantity_before,
                'quantity_after' => (float) $line->quantity_after,
                'batch_no' => $line->batch_no,
            ])->values(),
            'created_at' => $receipt->created_at?->toIso8601String(),
        ];
    }

    /**
     * Sequential per-prefix receipt number, e.g. GRN-2026-0007. Locked and
     * retried so two warehouses receiving at once cannot collide.
     */
    protected function nextNumber(string $prefix): string
    {
        $stem = $prefix . '-' . date('Y') . '-';

        $last = StockReceipt::where('receipt_no', 'like', $stem . '%')
            ->lockForUpdate()
            ->orderByDesc('receipt_no')
            ->value('receipt_no');

        $sequence = $last ? ((int) substr($last, strlen($stem))) + 1 : 1;

        do {
            $candidate = $stem . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
            $exists = StockReceipt::where('receipt_no', $candidate)->exists();
            $sequence++;
        } while ($exists);

        return $candidate;
    }

    /** Trim trailing zeros so "6.000" reads as "6". */
    protected function qty(float $value): string
    {
        return rtrim(rtrim(number_format($value, 3, '.', ''), '0'), '.') ?: '0';
    }
}
