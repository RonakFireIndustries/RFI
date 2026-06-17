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
        'latitude', 'longitude', 'status', 'site_manager_id'
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
