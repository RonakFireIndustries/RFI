<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Building detail children (wings, floors, flats, AMCs, fire systems, contacts,
 * status assignments) gain a `site_id` so a promoted Site owns them directly
 * instead of the data only being reachable through the building.
 *
 * `building_id` is intentionally left in place: the building stays the source
 * of truth for the survey, and the pipeline tables (site visits, opportunities,
 * follow-ups, quotations) still key off it. `site_id` is a denormalised mirror
 * written whenever a building is converted, so dropping a column later is a
 * separate, reversible decision.
 */
return new class extends Migration
{
    private const TABLES = [
        'building_wings',
        'building_floors',
        'building_flats',
        'building_amcs',
        'building_fire_systems',
        'fire_systems',
        'building_contacts',
        'building_status_mapping',
    ];

    public function up(): void
    {
        foreach (self::TABLES as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            if (Schema::hasColumn($tableName, 'site_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->unsignedBigInteger('site_id')->nullable()->index();
            });

            Schema::table($tableName, function (Blueprint $table) {
                $table->foreign('site_id')->references('id')->on('sites')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $tableName) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'site_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign(['site_id']);
            });

            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('site_id');
            });
        }
    }
};