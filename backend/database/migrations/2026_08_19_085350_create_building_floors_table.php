<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('building_floors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wing_id')->constrained('building_wings')->cascadeOnDelete();
            $table->string('name');
            $table->integer('floor_number')->default(0);
            $table->string('type')->nullable()->comment('Residential, Commercial, Parking, Terrace, etc.');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('building_floors');
    }
};
