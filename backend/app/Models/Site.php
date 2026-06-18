<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'code', 'client_details', 'address', 'city', 'state', 
        'country', 'pincode', 'contact_person', 'phone', 'email', 
        'latitude', 'longitude', 'allowed_radius', 'geo_fencing_enabled', 'status', 'site_manager_id'
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'allowed_radius' => 'integer',
        'geo_fencing_enabled' => 'boolean',
    ];

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'site_manager_id');
    }

    public function employeeSites(): HasMany
    {
        return $this->hasMany(EmployeeSite::class);
    }

    public function dailyReports(): HasMany
    {
        return $this->hasMany(DailyReport::class);
    }
}
