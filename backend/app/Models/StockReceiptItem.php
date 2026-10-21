<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single voucher line received on a stock receipt, with the running total
 * that line stood at after this receipt.
 *
 * `quantity_before` / `quantity_after` are the received (or delivered) totals
 * of the voucher line, so a line's history reads like a stock ledger and the
 * "when was it received, how much" question needs no recalculation.
 */
class StockReceiptItem extends Model
{
    protected $fillable = [
        'stock_receipt_id',
        'voucher_item_id',
        'product_id',
        'quantity',
        'quantity_before',
        'quantity_after',
        'transaction_ledger_id',
        'batch_no',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'quantity_before' => 'decimal:3',
        'quantity_after' => 'decimal:3',
        'product_id' => 'integer',
    ];

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(StockReceipt::class, 'stock_receipt_id');
    }

    public function voucherItem(): BelongsTo
    {
        return $this->belongsTo(VoucherItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(TransactionLedger::class, 'transaction_ledger_id');
    }
}
