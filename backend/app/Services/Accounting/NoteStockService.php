<?php

namespace App\Services\Accounting;

use App\Models\StockReceipt;
use App\Models\StockReceiptItem;
use App\Models\Voucher;
use App\Models\VoucherItem;
use App\Services\InventoryService;
use Illuminate\Support\Facades\DB;

/**
 * Stock movement for credit and debit notes (returns).
 *
 * Purchase and sales vouchers move stock in instalments through `stock_receipts`
 * — a supplier ships half in March and half in April. A return is not like
 * that: when a note is keyed, the goods are already back in (or out of) the
 * warehouse, so the movement is applied the moment the note is saved.
 *
 * The movement still lands in `stock_receipts`, one row per note, because that
 * is the register the warehouse reads and the ledger the reports aggregate. It
 * is simply written whole rather than in parts, and the note reports as
 * `complete` from the moment it exists.
 *
 *   credit note (customer returns goods)  -> inflow,  `sales_return`
 *   debit note  (goods go back to supplier) -> outflow, `purchase_return`
 *
 * An outbound note refuses to take stock negative, exactly like a delivery: a
 * return that the warehouse cannot physically make is a data error, and Option
 * B has no later step that would catch it.
 */
class NoteStockService
{
    public function __construct(protected InventoryService $inventory)
    {
    }

    /**
     * Whether this voucher type moves stock on save (as opposed to through the
     * receipt modal). Purchase and sales are fulfilled in parts; the notes are
     * not.
     */
    public static function handles(string $type): bool
    {
        return in_array($type, ['credit-note', 'debit-note'], true);
    }

    /**
     * Apply a note's stock movement, replacing any movement it made before.
     *
     * Called from both create and update, so it starts by undoing whatever the
     * note previously moved — an edit that changes a quantity must not leave
     * the old figure behind in `product_stock`.
     */
    public function apply(Voucher $voucher, int $userId, ?string $locationType = null, ?int $locationId = null): void
    {
        DB::transaction(function () use ($voucher, $userId, $locationType, $locationId) {
            $this->undo($voucher, $userId);

            $direction = $voucher->stockDirection();
            if ($direction === null) {
                return;
            }

            $outbound = $direction === 'out';
            $lines = $voucher->items()->whereNotNull('product_id')->get();

            if ($lines->isEmpty()) {
                return;
            }

            [$resolvedType, $resolvedId] = $this->resolveLocation($locationType, $locationId);

            $receipt = StockReceipt::create([
                'receipt_no' => $this->nextNumber($outbound ? 'DLV' : 'GRN'),
                'voucher_type' => $voucher->voucher_type,
                'voucher_id' => $voucher->id,
                'receipt_date' => $voucher->voucher_date,
                'party_ledger_id' => $voucher->party_ledger_id,
                'location_type' => $resolvedType,
                'location_id' => $resolvedId,
                'status' => 'active',
                'notes' => $outbound
                    ? "Goods returned to supplier on {$voucher->voucher_no}"
                    : "Goods returned by customer on {$voucher->voucher_no}",
                'received_by' => $userId,
            ]);

            foreach ($lines as $item) {
                $this->move($receipt, $item, $direction, $resolvedType, $resolvedId, $outbound);
            }
        });
    }

    /**
     * Undo whatever this note last moved, without erasing the history.
     *
     * The receipt rows stay (stamped `reversed`) so the register still shows
     * the note touched the warehouse; only the stock itself is given back.
     */
    public function undo(Voucher $voucher, int $userId): void
    {
        $receipts = StockReceipt::where('voucher_id', $voucher->id)
            ->where('status', 'active')
            ->with('items')
            ->get();

        foreach ($receipts as $receipt) {
            $outbound = $voucher->stockDirection() === 'out';

            foreach ($receipt->items as $line) {
                if (! $line->product_id) {
                    continue;
                }

                $this->inventory->recordTransaction(
                    productId: $line->product_id,
                    locationType: $receipt->location_type,
                    locationId: (int) $receipt->location_id,
                    // The opposite of the original move: a goods-in is given
                    // back with an adjustment, a goods-out comes back in as a
                    // sales return.
                    transactionType: $outbound ? 'sales_return' : 'adjustment',
                    quantity: (float) $line->quantity,
                    unitPrice: (float) ($line->voucherItem()->value('rate') ?? 0),
                    referenceType: 'receipt_reversal',
                    referenceId: $receipt->id,
                    notes: "{$voucher->voucher_no} re-posted or removed",
                );

                $item = $line->voucherItem()->first();
                if ($item) {
                    $column = $item->movementColumn();
                    $item->update([$column => 0]);
                }

                $line->update(['transaction_ledger_id' => null]);
            }

            $receipt->update([
                'status' => 'reversed',
                'reversed_by' => $userId,
                'reversed_at' => now(),
                'reversal_reason' => "{$voucher->voucher_no} re-posted",
            ]);
        }
    }

    /**
     * Record one line's movement and stamp the line's counter.
     */
    protected function move(
        StockReceipt $receipt,
        VoucherItem $item,
        string $direction,
        string $locationType,
        int $locationId,
        bool $outbound,
    ): void {
        $quantity = (float) $item->quantity;
        if ($quantity <= 0) {
            return;
        }

        $movement = $this->inventory->recordTransaction(
            productId: (int) $item->product_id,
            locationType: $locationType,
            locationId: $locationId,
            transactionType: $outbound ? 'purchase_return' : 'sales_return',
            quantity: $quantity,
            unitPrice: (float) $item->rate,
            referenceType: $outbound ? 'debit_note' : 'credit_note',
            referenceId: $receipt->id,
            notes: "{$receipt->receipt_no} — {$item->item_description}",
        );

        StockReceiptItem::create([
            'stock_receipt_id' => $receipt->id,
            'voucher_item_id' => $item->id,
            'product_id' => $item->product_id,
            'quantity' => $quantity,
            'quantity_before' => 0,
            'quantity_after' => $quantity,
            'transaction_ledger_id' => $movement->id,
        ]);

        $item->update([$item->movementColumn() => $quantity]);
    }

    /**
     * Where the goods moved. Defaults to the first site, matching the receipt
     * modal's default, so a note never fails merely because no location was
     * chosen.
     *
     * @return array{0: string, 1: int}
     */
    protected function resolveLocation(?string $locationType, ?int $locationId): array
    {
        if ($locationType && $locationId) {
            return [$locationType, $locationId];
        }

        $site = \App\Models\Site::query()->orderBy('id')->first();
        if ($site) {
            return [\App\Models\Site::class, (int) $site->id];
        }

        throw new \App\Exceptions\StockQuantityException(
            'No warehouse location exists to move stock to. Add a site first.'
        );
    }

    /** GRN-YYYY-NNNN / DLV-YYYY-NNNN, the same series the receipt modal uses. */
    protected function nextNumber(string $prefix): string
    {
        $year = now()->year;
        $stem = "{$prefix}-{$year}-";

        $last = StockReceipt::where('receipt_no', 'like', "{$stem}%")
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value('receipt_no');

        $next = $last ? ((int) substr($last, strlen($stem))) + 1 : 1;

        return $stem . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
