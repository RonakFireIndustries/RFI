<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('buildings', function (Blueprint $table) {
            $table->string('society_name')->nullable()->after('name');
            $table->string('building_type')->nullable()->after('society_name');
            $table->string('area')->nullable()->after('address');
            $table->string('google_place_id')->nullable()->after('pincode');
            $table->integer('construction_year')->nullable()->after('google_place_id');
            $table->integer('occupancy_certificate_year')->nullable()->after('construction_year');
            $table->string('fire_noc_status')->default('Unknown')->after('occupancy_certificate_year');
            $table->integer('basement_levels')->nullable()->after('no_of_floors');
            $table->boolean('terrace_available')->default(false)->after('basement_levels');
            $table->integer('refuge_floors')->nullable()->after('terrace_available');
            $table->text('equipment_condition_notes')->nullable()->after('fire_safety_type');
            $table->date('last_serviced_date')->nullable()->after('equipment_condition_notes');
            $table->string('last_amc_company')->nullable()->after('last_serviced_date');
            $table->string('overall_equipment_condition')->nullable()->after('last_amc_company');
        });
    }

    public function down(): void
    {
        Schema::table('buildings', function (Blueprint $table) {
            $table->dropColumn([
                'society_name', 'building_type', 'area', 'google_place_id',
                'construction_year', 'occupancy_certificate_year', 'fire_noc_status',
                'basement_levels', 'terrace_available', 'refuge_floors',
                'equipment_condition_notes', 'last_serviced_date', 'last_amc_company',
                'overall_equipment_condition',
            ]);
        });
    }
};
