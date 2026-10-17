<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Bill-wise detail: how much of a voucher settles a given bill.
 *
 * `bill_id` is intentionally not a foreign key — a bill may be a legacy
 * invoice / purchase order or (later) a sales / purchase voucher.
 */
class VoucherBillAllocation extends Model
{
    protected $fillable = [
        'voucher_id',
        'ledger_id',
        'bill_type',
        'bill_id',
        'bill_name',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'bill_id' => 'integer',
    ];

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    public function ledger()
    {
        return $this->belongsTo(LedgerAccount::class, 'ledger_id');
    }
}
