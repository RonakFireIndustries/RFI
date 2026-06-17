<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyReportHistory extends Model
{
    protected $fillable = [
        'daily_report_id',
        'action_by',
        'action',
        'comments'
    ];

    public function dailyReport(): BelongsTo
    {
        return $this->belongsTo(DailyReport::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'action_by');
    }
}
