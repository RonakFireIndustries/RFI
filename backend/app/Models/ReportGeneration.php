<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportGeneration extends Model
{
    protected $fillable = [
        'report_id', 'schedule_id', 'file_name', 'file_type', 'file_path',
        'file_size', 'generated_by', 'generated_at', 'parameters', 'status',
        'error_message',
    ];

    protected $casts = [
        'parameters' => 'array',
        'generated_at' => 'datetime',
        'file_size' => 'integer',
    ];

    public function report()
    {
        return $this->belongsTo(Report::class, 'report_id');
    }

    public function schedule()
    {
        return $this->belongsTo(ReportSchedule::class, 'schedule_id');
    }

    public function generator()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function getFileSizeForHumansAttribute()
    {
        $bytes = $this->file_size ?? 0;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
