<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SiteVisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'building_id', 'user_id', 'visit_date', 'purpose',
        'discussion_notes', 'next_followup_date',
    ];

    protected $casts = [
        'visit_date' => 'date',
        'next_followup_date' => 'date',
    ];

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(SiteVisitPhoto::class);
    }

    public function voiceNotes(): HasMany
    {
        return $this->hasMany(SiteVisitVoiceNote::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(SalesDocument::class);
    }
}
