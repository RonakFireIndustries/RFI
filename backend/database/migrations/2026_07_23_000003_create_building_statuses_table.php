<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('building_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('color')->default('#6B7280');
            $table->string('marker_color')->nullable();
            $table->timestamps();
        });

        Schema::create('building_status_mapping', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id')->constrained()->cascadeOnDelete();
            $table->foreignId('building_status_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['building_id', 'building_status_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('building_status_mapping');
        Schema::dropIfExists('building_statuses');
    }
};
