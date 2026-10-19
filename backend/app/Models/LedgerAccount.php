<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A ledger account — the unit every voucher posts Dr/Cr against.
 */
class LedgerAccount extends Model
{
    protected $fillable = [
        'name',
        'group_id',
        'opening_balance',
        'opening_type',
        'is_bank',
        'is_cash',
        'bank_name',
        'bank_account_number',
        'bank_ifsc',
        'bank_branch',
        'gst_number',
        'gst_type',
        'pan_number',
        'party_type',
        'party_id',
        'is_system',
        'status',
        'notes',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'is_bank' => 'boolean',
        'is_cash' => 'boolean',
        'is_system' => 'boolean',
    ];

    public function group()
    {
        return $this->belongsTo(AccountGroup::class, 'group_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'party_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'party_id');
    }

    /** Resolve the linked party master when this is a party ledger. */
    public function getPartyAttribute()
    {
        if (! $this->party_id) {
            return null;
        }

        return match ($this->party_type) {
            'customer' => $this->customer,
            'supplier' => $this->supplier,
            default => null,
        };
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /** First ledger whose name matches (case-insensitive), used for defaults. */
    public static function findByName(?string $name): ?self
    {
        if (! $name) {
            return null;
        }

        return static::whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->first();
    }
}
