<?php

namespace App\Services;

use App\Models\FinancialYear;
use App\Models\LedgerAccount;
use App\Models\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

/**
 * Order tracking: numbering, line totals, and the status pipeline.
 *
 * Nothing in here writes to the ledger — an order is operational only. When
 * it is marked invoiced it simply records which bill it became (see
 * config/orders.php for the stages and the document types each side may
 * link), and everything money-related is read back from the accounting
 * services at render time.
 */
class OrderService
{
    /* ------------------------------ create ------------------------------ */

    public function create(array $data, int $userId): Order
    {
        $side = $data['side'];
        $this->assertParty($side, $data);

        return DB::transaction(function () use ($data, $side, $userId) {
            $items = $this->normaliseItems($data['items']);

            $order = Order::create(array_merge([
                'order_no' => $this->nextNumber($side, $data['order_date']),
                'side' => $side,
                'customer_id' => $data['customer_id'] ?? null,
                'supplier_id' => $data['supplier_id'] ?? null,
                'created_by' => $userId,
                'order_date' => $data['order_date'],
                'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
                'status' => 'open',
                'reference_no' => $data['reference_no'] ?? null,
                'notes' => $data['notes'] ?? null,
            ], $this->totals($items)));

            $this->saveItems($order, $items);
            $order->events()->create([
                'from_status' => null,
                'to_status' => 'open',
                'user_id' => $userId,
                'note' => 'Order created',
            ]);

            return $order->load(['items', 'events', 'customer:id,name', 'supplier:id,name']);
        });
    }

    /* ------------------------------ update ------------------------------ */

    public function update(Order $order, array $data, int $userId): Order
    {
        // While the order is still in flight its party, dates, notes and lines
        // can all move; once it is fulfilled only the document link may be
        // corrected (a mis-picked invoice must be fixable, but the record of
        // what was agreed may not be rewritten after fulfilment).
        if ($order->isEditable()) {
            $side = $data['side'] ?? $order->side;
            if ($side !== $order->side) {
                throw ValidationException::withMessages([
                    'side' => 'An order keeps the side it was raised on.',
                ]);
            }

            $this->assertParty($side, array_merge(
                ['customer_id' => $order->customer_id, 'supplier_id' => $order->supplier_id],
                $data
            ));

            return DB::transaction(function () use ($order, $data) {
                $items = isset($data['items']) ? $this->normaliseItems($data['items']) : null;

                $attributes = [
                    'customer_id' => $data['customer_id'] ?? $order->customer_id,
                    'supplier_id' => $data['supplier_id'] ?? $order->supplier_id,
                    'order_date' => $data['order_date'] ?? $order->order_date,
                ];

                // These may be cleared, so an explicit null has to win over the
                // stored value — `?? $order->…` would quietly keep the old one.
                foreach (['expected_delivery_date', 'reference_no', 'notes'] as $clearable) {
                    if (array_key_exists($clearable, $data)) {
                        $attributes[$clearable] = $data[$clearable];
                    }
                }

                if ($items !== null) {
                    $attributes = array_merge($attributes, $this->totals($items));
                    $this->saveItems($order, $items);
                }

                $order->update($attributes);

                return $order->fresh()->load(['items', 'events', 'customer:id,name', 'supplier:id,name']);
            });
        }

        if ($order->status === 'invoiced') {
            // Only a re-pick of the document is allowed past fulfilment; a
            // payload without one (notes, say) changes nothing rather than
            // demanding a document that was already linked.
            if (array_key_exists('document_type', $data) || array_key_exists('document_id', $data)) {
                $this->assertDocument($order, $data['document_type'] ?? null, $data['document_id'] ?? null);

                $order->update([
                    'document_type' => $data['document_type'],
                    'document_id' => (int) $data['document_id'],
                ]);
            }

            return $order->fresh()->load(['items', 'events', 'customer:id,name', 'supplier:id,name']);
        }

        throw ValidationException::withMessages([
            'status' => 'A ' . $this->statusLabel($order->status) . ' order can no longer be edited.',
        ]);
    }

    /* ------------------------------ pipeline ---------------------------- */

    /** Walk the order one stage along `open → in_progress → … → done`. */
    public function advance(Order $order, array $data, int $userId): Order
    {
        $next = config("orders.statuses.{$order->status}.next");
        if ($next === null) {
            throw ValidationException::withMessages([
                'status' => 'A ' . $this->statusLabel($order->status) . ' order cannot be advanced.',
            ]);
        }

        // Reaching `invoiced` is what ties the order to a bill, so the link is
        // required at that moment — the payment panel reads it afterwards.
        if ($next === 'invoiced') {
            $this->assertDocument($order, $data['document_type'] ?? null, $data['document_id'] ?? null);
        }

        return DB::transaction(function () use ($order, $data, $next, $userId) {
            $from = $order->status;

            $order->update($next === 'invoiced'
                ? [
                    'status' => 'invoiced',
                    'document_type' => $data['document_type'],
                    'document_id' => (int) $data['document_id'],
                ]
                : ['status' => $next]);

            $order->events()->create([
                'from_status' => $from,
                'to_status' => $next,
                'user_id' => $userId,
                'note' => $data['note'] ?? null,
            ]);

            return $order->fresh()->load(['items', 'events', 'customer:id,name', 'supplier:id,name']);
        });
    }

    public function cancel(Order $order, ?string $note, int $userId): Order
    {
        if (! $order->isCancellable()) {
            throw ValidationException::withMessages([
                'status' => 'A ' . $this->statusLabel($order->status) . ' order cannot be cancelled.',
            ]);
        }

        $from = $order->status;
        $order->update(['status' => 'cancelled']);
        $order->events()->create([
            'from_status' => $from,
            'to_status' => 'cancelled',
            'user_id' => $userId,
            'note' => $note,
        ]);

        return $order->fresh()->load(['items', 'events', 'customer:id,name', 'supplier:id,name']);
    }

    /* ------------------------------- lines ------------------------------ */

    /**
     * One line's arithmetic, mirroring the voucher item maths but keeping a
     * single tax figure: the CGST/SGST split belongs to the GST invoice, not
     * to an operational order.
     *
     * @return array<int, array<string, mixed>>
     */
    public function normaliseItems(array $items): array
    {
        if (! is_array($items) || count($items) < 1) {
            throw ValidationException::withMessages(['items' => 'Add at least one line item.']);
        }

        $out = [];
        foreach (array_values($items) as $i) {
            $qty = round((float) ($i['quantity'] ?? 0), 3);
            $rate = round((float) ($i['rate'] ?? 0), 2);
            $discPct = round((float) ($i['discount_percent'] ?? 0), 2);
            $taxRate = round((float) ($i['tax_rate'] ?? 0), 2);

            if ($qty <= 0) {
                throw ValidationException::withMessages([
                    'items' => 'Every line needs a quantity greater than zero.',
                ]);
            }

            $gross = round($qty * $rate, 2);
            $disc = round($gross * $discPct / 100, 2);
            $taxable = round($gross - $disc, 2);
            $tax = round($taxable * $taxRate / 100, 2);

            $out[] = [
                'product_id' => $i['product_id'] ?? null,
                'item_description' => trim((string) ($i['item_description'] ?? '')),
                'hsn_code' => $i['hsn_code'] ?? null,
                'unit' => $i['unit'] ?? null,
                'quantity' => $qty,
                'rate' => $rate,
                'discount_percent' => $discPct,
                'tax_rate' => $taxRate,
                'taxable_amount' => $taxable,
                'discount_amount' => $disc,
                'tax_amount' => $tax,
                'total_amount' => round($taxable + $tax, 2),
            ];
        }

        return $out;
    }

    /** @param array<int, array<string, mixed>> $items */
    protected function totals(array $items): array
    {
        return [
            'subtotal' => round(array_sum(array_column($items, 'taxable_amount')), 2),
            'discount_total' => round(array_sum(array_column($items, 'discount_amount')), 2),
            'tax_total' => round(array_sum(array_column($items, 'tax_amount')), 2),
            'total_amount' => round(array_sum(array_column($items, 'total_amount')), 2),
        ];
    }

    protected function saveItems(Order $order, array $items): void
    {
        $order->items()->delete();

        foreach (array_values($items) as $position => $item) {
            $order->items()->create($item + ['position' => $position]);
        }
    }

    /* ----------------------------- validation --------------------------- */

    /** Each side is for exactly one party master: customer vs supplier. */
    protected function assertParty(string $side, array $data): void
    {
        $key = config("orders.sides.{$side}.party") . '_id';
        $label = config("orders.sides.{$side}.party");

        if (empty($data[$key])) {
            throw ValidationException::withMessages([
                $key => 'Pick the ' . $label . ' this order is for.',
            ]);
        }
    }

    /**
     * The invoice/voucher an order may be marked invoiced against: a type the
     * side allows, an existing row of the right document, and one that belongs
     * to this order's party (the payment panel would otherwise report another
     * party's bill).
     */
    protected function assertDocument(Order $order, mixed $type, mixed $id): void
    {
        $allowed = config("orders.sides.{$order->side}.documents", []);
        $id = (int) $id;

        if (! is_string($type) || ! in_array($type, $allowed, true) || $id <= 0) {
            throw ValidationException::withMessages([
                'document_id' => 'Pick the ' . strtolower(config("orders.sides.{$order->side}.label"))
                    . ' document this order became.',
            ]);
        }

        $table = [
            'invoice' => 'invoices',
            'purchase-order' => 'purchase_orders',
            'sales-voucher' => 'vouchers',
            'purchase-voucher' => 'vouchers',
        ][$type] ?? null;

        if ($table === null || ! Schema::hasTable($table)) {
            throw ValidationException::withMessages(['document_id' => 'That document does not exist.']);
        }

        $query = DB::table($table)->where('id', $id);
        if ($type === 'sales-voucher' || $type === 'purchase-voucher') {
            $query->where('voucher_type', str_contains($type, 'sales') ? 'sales' : 'purchase');
        }

        if (! $query->exists()) {
            throw ValidationException::withMessages(['document_id' => 'That document does not exist.']);
        }

        $this->assertDocumentParty($order, $type, $table, $id);
    }

    /** Confirm the linked document belongs to the order's own party. */
    protected function assertDocumentParty(Order $order, string $type, string $table, int $id): void
    {
        $row = DB::table($table)->where('id', $id)->first([
            ...$type === 'invoice' ? ['customer_id'] : [],
            ...$type === 'purchase-order' ? ['supplier_id'] : [],
            ...$type === 'sales-voucher' || $type === 'purchase-voucher' ? ['party_ledger_id'] : [],
        ]);

        $expected = $type === 'invoice' || $type === 'purchase-order'
            ? ($order->side === 'sales' ? $order->customer_id : $order->supplier_id)
            : null;

        if ($expected !== null && $row !== null
            && (int) ($type === 'invoice' ? $row->customer_id : $row->supplier_id) !== (int) $expected) {
            throw ValidationException::withMessages([
                'document_id' => 'That document belongs to a different party.',
            ]);
        }

        // Vouchers point at a ledger rather than a master row; match it when
        // the ledger is linked (an unlinked ledger cannot be disproved).
        if (($type === 'sales-voucher' || $type === 'purchase-voucher') && $row?->party_ledger_id) {
            $ledger = LedgerAccount::find($row->party_ledger_id);
            $matches = $ledger && $ledger->party_id !== null
                && $ledger->party_type === ($order->side === 'sales' ? 'customer' : 'supplier')
                && (int) $ledger->party_id === (int) ($order->side === 'sales' ? $order->customer_id : $order->supplier_id);

            if ($ledger !== null && $ledger->party_id !== null && ! $matches) {
                throw ValidationException::withMessages([
                    'document_id' => 'That document belongs to a different party.',
                ]);
            }
        }
    }

    /* ----------------------------- numbering ---------------------------- */

    /** `SO-2026-0001`, restarting each financial year like voucher numbers. */
    protected function nextNumber(string $side, string $date): string
    {
        $prefix = config("orders.prefixes.{$side}", strtoupper($side)) . '-';
        $stem = $prefix . $this->resolveFinancialYear($date) . '-';

        $last = Order::where('order_no', 'like', $stem . '%')->orderByDesc('order_no')->value('order_no');
        $sequence = $last ? ((int) substr($last, strlen($stem))) + 1 : 1;

        do {
            $candidate = $stem . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
            $exists = Order::where('order_no', $candidate)->exists();
            $sequence++;
        } while ($exists);

        return $candidate;
    }

    /** Start year of the financial year holding the date, else the active one, else the calendar year. */
    protected function resolveFinancialYear(string $date): int
    {
        $year = FinancialYear::where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->first()
            ?? FinancialYear::active();

        return $year ? (int) $year->start_date->format('Y') : (int) Carbon::parse($date)->format('Y');
    }

    protected function statusLabel(string $status): string
    {
        return strtolower(config("orders.statuses.{$status}.label", $status));
    }
}
