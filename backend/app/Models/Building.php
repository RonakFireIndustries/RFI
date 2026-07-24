<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Building extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'society_name', 'building_type', 'address', 'area', 'city', 'state', 'country', 'pincode',
        'latitude', 'longitude', 'google_place_id',
        'no_of_floors', 'no_of_wings', 'no_of_flats',
        'commercial_shops_available',
        'no_of_staircase', 'no_of_lifts', 'no_of_exits_entry',
        'fire_safety_available', 'fire_safety_type',
        'under_construction', 'property_owner', 'plot_no',
        'developer_name', 'developer_contact',
        'architect_name', 'architect_contact',
        'pmc_consultant_name', 'pmc_consultant_contact',
        'provisional_noc', 'provisional_noc_details',
        'site_id', 'status',
        'construction_year', 'occupancy_certificate_year', 'fire_noc_status',
        'basement_levels', 'terrace_available', 'refuge_floors',
        'equipment_condition_notes', 'last_serviced_date', 'last_amc_company', 'overall_equipment_condition',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'no_of_floors' => 'integer',
        'no_of_flats' => 'integer',
        'no_of_staircase' => 'integer',
        'no_of_lifts' => 'integer',
        'no_of_exits_entry' => 'integer',
        'commercial_shops_available' => 'boolean',
        'fire_safety_available' => 'boolean',
        'under_construction' => 'boolean',
        'provisional_noc' => 'boolean',
        'construction_year' => 'integer',
        'occupancy_certificate_year' => 'integer',
        'basement_levels' => 'integer',
        'terrace_available' => 'boolean',
        'refuge_floors' => 'integer',
        'last_serviced_date' => 'date',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function wings(): HasMany
    {
        return $this->hasMany(BuildingWing::class);
    }

    public function statuses(): BelongsToMany
    {
        return $this->belongsToMany(BuildingStatus::class, 'building_status_mapping');
    }

    public function fireSystems(): HasMany
    {
        return $this->hasMany(FireSystem::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(BuildingContact::class);
    }

    public function siteVisits(): HasMany
    {
        return $this->hasMany(SiteVisit::class);
    }

    public function opportunities(): HasMany
    {
        return $this->hasMany(Opportunity::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(SalesDocument::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'loggable_id')
            ->where('loggable_type', self::class);
    }

    public function activeOpportunity(): HasOne
    {
        return $this->hasOne(Opportunity::class)->where('status', 'Active');
    }
}
