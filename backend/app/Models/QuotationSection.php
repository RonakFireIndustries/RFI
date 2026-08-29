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
     * Sub-total of every item inside this section.
     */
    public function getSubtotalAttribute(): float
    {
        return (float) $this->items->sum('amount');
    }
}
