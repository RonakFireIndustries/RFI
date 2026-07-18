<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buildings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable()->default('India');
            $table->string('pincode')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            $table->unsignedInteger('no_of_floors')->nullable();
            $table->string('no_of_wings')->nullable();
            $table->unsignedInteger('no_of_flats')->nullable();
            $table->boolean('commercial_shops_available')->default(false);
            $table->unsignedInteger('no_of_staircase')->nullable();
            $table->unsignedInteger('no_of_lifts')->nullable();
            $table->unsignedInteger('no_of_exits_entry')->nullable();

            $table->boolean('fire_safety_available')->default(false);
            $table->string('fire_safety_type')->nullable();

            $table->boolean('under_construction')->default(false);
            $table->string('property_owner')->nullable();
            $table->string('plot_no')->nullable();
            $table->string('developer_name')->nullable();
            $table->string('developer_contact')->nullable();
            $table->string('architect_name')->nullable();
            $table->string('architect_contact')->nullable();
            $table->string('pmc_consultant_name')->nullable();
            $table->string('pmc_consultant_contact')->nullable();
            $table->boolean('provisional_noc')->default(false);
            $table->text('provisional_noc_details')->nullable();

            $table->unsignedBigInteger('site_id')->nullable();
            $table->foreign('site_id')->references('id')->on('sites')->nullOnDelete();

            $table->string('status')->default('Active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buildings');
    }
};
