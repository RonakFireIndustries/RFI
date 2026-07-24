<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fire_systems', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id')->constrained()->cascadeOnDelete();
            $table->string('system_type');
            $table->string('sub_type')->nullable();
            $table->integer('quantity')->nullable();
            $table->string('capacity')->nullable();
            $table->string('brand')->nullable();
            $table->integer('installation_year')->nullable();
            $table->date('last_testing_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fire_systems');
    }
};
