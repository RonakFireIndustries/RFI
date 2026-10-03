<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lets a building be promoted into a fully working Site.
 *
 * A converted Site has to stand on its own: attendance, geo-fencing and stock
 * movements are all driven from `sites`, while the fire-safety and structural
 * survey of a building lives on `buildings`. Adding the survey columns here
 * means the promoted Site keeps that detail without a join back to the
 * building it came from.
 *
 * Every column is nullable and additive so existing Site rows are untouched.
 */
return new class extends Migration
{
    /** Column name => type. */
    private const COLUMNS = [
        'society_name' => 'string',
        'building_type' => 'string',
        'area' => 'string',
        'google_place_id' => 'string',
        'construction_year' => 'integer',
        'occupancy_certificate_year' => 'integer',
        'fire_noc_status' => 'string',
        'no_of_floors' => 'unsignedInteger',
        'basement_levels' => 'integer',
        'no_of_wings' => 'string',
        'no_of_flats' => 'unsignedInteger',
        'terrace_available' => 'boolean',
        'refuge_floors' => 'integer',
        'commercial_shops_available' => 'boolean',
        'no_of_staircase' => 'unsignedInteger',
        'no_of_lifts' => 'unsignedInteger',
        'no_of_exits_entry' => 'unsignedInteger',
        'fire_safety_available' => 'boolean',
        'fire_safety_type' => 'string',
        'equipment_condition_notes' => 'text',
        'last_serviced_date' => 'date',
        'last_amc_company' => 'string',
        'overall_equipment_condition' => 'string',
        'property_owner' => 'string',
        'plot_no' => 'string',
        'contact_name' => 'string',
        'contact_number' => 'string',
        'developer_name' => 'string',
        'developer_contact' => 'string',
        'architect_name' => 'string',
        'architect_contact' => 'string',
        'pmc_consultant_name' => 'string',
        'pmc_consultant_contact' => 'string',
        'provisional_noc' => 'boolean',
        'provisional_noc_details' => 'text',
        'under_construction' => 'boolean',
    ];

    /** Columns that mirror a default rather than defaulting to null. */
    private const DEFAULTS = [
        'fire_noc_status' => 'Unknown',
    ];

    private function addColumn(Blueprint $table, string $name, string $type): void
    {
        $column = $table->{$type}($name)->nullable();

        if (array_key_exists($name, self::DEFAULTS)) {
            $column->default(self::DEFAULTS[$name]);
        }
    }

    public function up(): void
    {
        if (! Schema::hasTable('sites')) {
            return;
        }

        $existing = Schema::getColumnListing('sites');

        // Added in batches so a single ALTER never grows unbounded.
        foreach (array_chunk(array_keys(self::COLUMNS), 10) as $batch) {
            Schema::table('sites', function (Blueprint $table) use ($batch, $existing) {
                foreach ($batch as $name) {
                    if (in_array($name, $existing, true)) {
                        continue;
                    }

                    $this->addColumn($table, $name, self::COLUMNS[$name]);
                }
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('sites')) {
            return;
        }

        $existing = Schema::getColumnListing('sites');
        $drop = array_values(array_intersect(array_keys(self::COLUMNS), $existing));

        foreach (array_chunk($drop, 10) as $batch) {
            Schema::table('sites', function (Blueprint $table) use ($batch) {
                $table->dropColumn($batch);
            });
        }
    }
};