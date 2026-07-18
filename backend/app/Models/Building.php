<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Building extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'address', 'city', 'state', 'country', 'pincode',
        'latitude', 'longitude',
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
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
