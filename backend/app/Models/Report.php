<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = [
        'category_id', 'name', 'slug', 'description', 'icon', 'route',
        'api_endpoint', 'status', 'created_by', 'last_generated_at',
        'parameters', 'sort_order',
    ];

    protected $casts = [
        'parameters' => 'array',
        'last_generated_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(ReportCategory::class, 'category_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function schedules()
    {
        return $this->hasMany(ReportSchedule::class, 'report_id');
    }

    public function generations()
    {
        return $this->hasMany(ReportGeneration::class, 'report_id');
    }

    public function activeSchedules()
    {
        return $this->hasMany(ReportSchedule::class, 'report_id')->where('status', 'active');
    }

    public function latestGeneration()
    {
        return $this->hasOne(ReportGeneration::class, 'report_id')->latestOfMany('generated_at');
    }
}
