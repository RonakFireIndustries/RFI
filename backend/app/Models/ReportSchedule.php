<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportSchedule extends Model
{
    protected $fillable = [
        'report_id', 'name', 'frequency', 'config', 'next_run_at',
        'last_run_at', 'status', 'created_by', 'recipients', 'format',
    ];

    protected $casts = [
        'config' => 'array',
        'next_run_at' => 'datetime',
        'last_run_at' => 'datetime',
    ];

    public function report()
    {
        return $this->belongsTo(Report::class, 'report_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function generations()
    {
        return $this->hasMany(ReportGeneration::class, 'schedule_id');
    }
}
