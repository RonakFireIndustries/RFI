<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DashboardWidget extends Model
{
    protected $fillable = [
        'widget_key', 'name', 'type', 'icon', 'chart_type',
        'config', 'permission', 'order', 'is_active',
    ];

    protected $casts = [
        'config' => 'json',
        'is_active' => 'boolean',
    ];

    public function designations()
    {
        return $this->belongsToMany(Designation::class, 'dashboard_widget_designation');
    }
}
