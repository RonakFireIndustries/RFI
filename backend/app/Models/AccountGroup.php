<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A Tally-style account group. Groups classify ledgers into the four natures
 * and carry the direct/indirect (gross-profit) split used by the P&L.
 */
class AccountGroup extends Model
{
    protected $fillable = [
        'name',
        'parent_id',
        'nature',
        'is_primary',
        'affects_gross_profit',
        'is_party_group',
        'party_type',
        'is_system',
        'sort_order',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'affects_gross_profit' => 'boolean',
        'is_party_group' => 'boolean',
        'is_system' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function parent()
    {
        return $this->belongsTo(AccountGroup::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(AccountGroup::class, 'parent_id')
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    public function ledgers()
    {
        return $this->hasMany(LedgerAccount::class, 'group_id');
    }
}
