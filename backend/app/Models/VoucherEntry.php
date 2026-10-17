<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A single Dr or Cr line of a voucher.
 */
class VoucherEntry extends Model
{
    protected $fillable = [
        'voucher_id',
        'line_no',
        'ledger_id',
        'entry_type',
        'amount',
        'narration',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'line_no' => 'integer',
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
