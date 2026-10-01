<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

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

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'dashboard_widget_role');
    }
}
