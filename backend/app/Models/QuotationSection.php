<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuotationSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_id',
        'name',
        'sort_order',
    ];

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class, 'quotation_section_id')
            ->orderBy('id');
    }

    /**
     * Sub-total of every item inside this section (supply + installation).
     */
    public function getSubtotalAttribute(): float
    {
        return round((float) $this->items->sum('amount'), 2);
    }

    /**
     * Supply component of the section: sum of qty * rate.
     */
    public function getSupplyAmountAttribute(): float
    {
        return round((float) $this->items->sum(fn ($i) => $i->supply_amount), 2);
    }

    /**
     * Installation component of the section: sum of qty * installment.
     */
    public function getInstallationAmountAttribute(): float
    {
        return round((float) $this->items->sum(fn ($i) => $i->installation_amount), 2);
    }

    /**
     * Section total: supply + installation.
     */
    public function getTotalAmountAttribute(): float
    {
        return round($this->supply_amount + $this->installation_amount, 2);
    }
}
