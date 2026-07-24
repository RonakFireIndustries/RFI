<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('building_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id')->constrained()->cascadeOnDelete();
            $table->string('full_name');
            $table->string('role');
            $table->string('role_category')->default('society');
            $table->string('mobile_number')->nullable();
            $table->string('whatsapp_number')->nullable();
            $table->string('email')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('building_contacts');
    }
};
