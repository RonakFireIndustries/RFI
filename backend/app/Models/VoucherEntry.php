<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A single Dr or Cr line of a voucher.
 *
 * Slice 4 added the reconciliation columns: a line is "cleared" once it has a
 * `reconciled_at` date, which is what the bank reconciliation statement uses to
 * separate cleared items from those still in transit.
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
        'reconciled_at',
        'reconciled_by',
        'reconciliation_note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'line_no' => 'integer',
        'reconciled_at' => 'date',
    ];

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    public function ledger()
    {
        return $this->belongsTo(LedgerAccount::class, 'ledger_id');
    }

    public function reconciledBy()
    {
        return $this->belongsTo(User::class, 'reconciled_by');
    }

    public function scopeReconciled($query)
    {
        return $query->whereNotNull('reconciled_at');
    }

    public function scopeUnreconciled($query)
    {
        return $query->whereNull('reconciled_at');
    }
}
