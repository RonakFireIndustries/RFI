<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FollowUp extends Model
{
    use HasFactory;

    protected $fillable = [
        'building_id', 'opportunity_id', 'site_visit_id', 'building_contact_id',
        'user_id', 'reminder_date', 'reminder_time', 'type', 'notes', 'status',
    ];

    protected $casts = [
        'reminder_date' => 'date',
    ];

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }

    public function siteVisit(): BelongsTo
    {
        return $this->belongsTo(SiteVisit::class);
    }

    public function buildingContact(): BelongsTo
    {
        return $this->belongsTo(BuildingContact::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
