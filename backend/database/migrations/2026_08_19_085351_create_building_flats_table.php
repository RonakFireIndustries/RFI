<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('building_flats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wing_id')->constrained('building_wings')->cascadeOnDelete();
            $table->foreignId('floor_id')->constrained('building_floors')->cascadeOnDelete();
            $table->string('name');
            $table->string('flat_number')->nullable();
            $table->string('bhk_type')->nullable()->comment('1BHK, 2BHK, 3BHK, etc.');
            $table->decimal('area', 10, 2)->nullable()->comment('Area in sqft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('building_flats');
    }
};
