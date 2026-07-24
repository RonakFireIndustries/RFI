<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'building_id', 'site_visit_id', 'opportunity_id',
        'name', 'category', 'file_type', 'file_size', 'file_path',
        'uploaded_by', 'expiry_date', 'version_number', 'parent_document_id',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'version_number' => 'integer',
        'expiry_date' => 'date',
    ];

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function siteVisit(): BelongsTo
    {
        return $this->belongsTo(SiteVisit::class);
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function parentDocument(): BelongsTo
    {
        return $this->belongsTo(SalesDocument::class, 'parent_document_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(SalesDocument::class, 'parent_document_id');
    }

    public function getUrlAttribute(): string
    {
        return '/storage/' . $this->file_path;
    }
}
