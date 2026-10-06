<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExtinguisherMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'fire_system_id', 'action', 'remark', 'next_refill_date', 'user_id',
    ];

    protected $casts = [
        'next_refill_date' => 'date',
    ];

    public const ACTION_TAKEN = 'taken_for_refilling';
    public const ACTION_RETURNED = 'returned';
    public const ACTION_DAMAGED = 'marked_damaged';
    public const ACTION_DECOMMISSIONED = 'decommissioned';
    public const ACTION_RESTORED = 'restored';

    public function extinguisher(): BelongsTo
    {
        return $this->belongsTo(FireSystem::class, 'fire_system_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
