<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Opportunity extends Model
{
    use HasFactory;

    protected $fillable = [
        'building_id', 'user_id', 'name', 'estimated_value', 'probability',
        'stage', 'expected_closing_date', 'notes',
        'actual_final_value', 'project_start_date', 'lost_reason', 'status',
    ];

    protected $casts = [
        'estimated_value' => 'decimal:2',
        'probability' => 'integer',
        'expected_closing_date' => 'date',
        'actual_final_value' => 'decimal:2',
        'project_start_date' => 'date',
    ];

    public const STAGES = ['Prospect', 'Follow-Up', 'Quotation Sent', 'Negotiation', 'Won', 'Lost'];

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workTypes(): HasMany
    {
        return $this->hasMany(OpportunityWorkType::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(SalesDocument::class);
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class);
    }
}
