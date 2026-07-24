<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteVisitVoiceNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_visit_id', 'file_name', 'file_path', 'mime_type', 'file_size', 'duration_seconds', 'uploaded_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'duration_seconds' => 'integer',
    ];

    public function siteVisit(): BelongsTo
    {
        return $this->belongsTo(SiteVisit::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getUrlAttribute(): string
    {
        return '/storage/' . $this->file_path;
    }
}
