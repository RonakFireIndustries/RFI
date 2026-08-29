<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_no',
        'building_id',
        'building_name',
        'quotation_date',
        'status',
        'discount',
        'gst_percent',
        'terms',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'quotation_date' => 'date',
        'discount' => 'decimal:2',
        'gst_percent' => 'decimal:2',
    ];

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SENT = 'sent';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Sub-total before discount/GST = sum of line amounts.
     */
    public function getSubtotalAttribute(): float
    {
        return (float) $this->items->sum('amount');
    }

    /**
     * Grand total after discount and GST.
     */
    public function getGrandTotalAttribute(): float
    {
        $subtotal = $this->subtotal;
        $discount = (float) ($this->discount ?? 0);
        $gst = (float) ($this->gst_percent ?? 0);
        $afterDiscount = $subtotal - $discount;
        return round($afterDiscount + ($afterDiscount * $gst / 100), 2);
    }

    /**
     * Display building name: explicit name or linked building's name.
     */
    public function getDisplayBuildingNameAttribute(): ?string
    {
        if (!empty($this->building_name)) {
            return $this->building_name;
        }
        return $this->building?->name;
    }
}
