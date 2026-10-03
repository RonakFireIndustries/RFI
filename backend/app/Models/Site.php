<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Site extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'code', 'client_details', 'address', 'city', 'state',
        'country', 'pincode', 'contact_person', 'phone', 'email',
        'latitude', 'longitude', 'allowed_radius', 'geo_fencing_enabled',
        'status', 'site_manager_id', 'start_date', 'end_date',
        // Building survey profile - populated when a building is promoted into
        // a site, and otherwise left null for sites created from scratch.
        'society_name', 'building_type', 'area', 'google_place_id',
        'construction_year', 'occupancy_certificate_year', 'fire_noc_status',
        'no_of_floors', 'basement_levels', 'no_of_wings', 'no_of_flats',
        'terrace_available', 'refuge_floors', 'commercial_shops_available',
        'no_of_staircase', 'no_of_lifts', 'no_of_exits_entry',
        'fire_safety_available', 'fire_safety_type', 'equipment_condition_notes',
        'last_serviced_date', 'last_amc_company', 'overall_equipment_condition',
        'property_owner', 'plot_no', 'contact_name', 'contact_number',
        'developer_name', 'developer_contact', 'architect_name', 'architect_contact',
        'pmc_consultant_name', 'pmc_consultant_contact',
        'provisional_noc', 'provisional_noc_details', 'under_construction',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'allowed_radius' => 'integer',
        'geo_fencing_enabled' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'construction_year' => 'integer',
        'occupancy_certificate_year' => 'integer',
        'no_of_floors' => 'integer',
        'basement_levels' => 'integer',
        'no_of_flats' => 'integer',
        'terrace_available' => 'boolean',
        'refuge_floors' => 'integer',
        'commercial_shops_available' => 'boolean',
        'no_of_staircase' => 'integer',
        'no_of_lifts' => 'integer',
        'no_of_exits_entry' => 'integer',
        'fire_safety_available' => 'boolean',
        'provisional_noc' => 'boolean',
        'under_construction' => 'boolean',
        'last_serviced_date' => 'date',
    ];

    /**
     * Buildings that were promoted into this site.
     *
     * Normally one, but the relation is a hasMany because `site_id` is not
     * unique and a site created by hand can later have buildings converted
     * into it.
     */
    public function sourceBuildings(): HasMany
    {
        return $this->hasMany(Building::class, 'site_id');
    }

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

    public function stock(): MorphMany
    {
        return $this->morphMany(ProductStock::class, 'locationable', 'location_type', 'location_id');
    }

    public function outgoingTransactions(): MorphMany
    {
        return $this->morphMany(TransactionLedger::class, 'locationable', 'location_type', 'location_id');
    }

    public function incomingTransactions(): MorphMany
    {
        return $this->morphMany(TransactionLedger::class, 'to_locationable', 'to_location_type', 'to_location_id');
    }

    public function outgoingStockRequests(): MorphMany
    {
        return $this->morphMany(StockRequest::class, 'from_locationable', 'from_location_type', 'from_location_id');
    }

    public function incomingStockRequests(): MorphMany
    {
        return $this->morphMany(StockRequest::class, 'to_locationable', 'to_location_type', 'to_location_id');
    }
}
