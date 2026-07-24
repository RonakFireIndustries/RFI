<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('building_wings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->integer('floors')->default(0);
            $table->integer('flats_per_floor')->default(0);
            $table->string('flat_configuration')->nullable();
            $table->integer('total_flats')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('building_wings');
    }
};
