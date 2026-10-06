<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extinguisher_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fire_system_id')->constrained()->cascadeOnDelete();
            $table->string('action');
            $table->text('remark')->nullable();
            $table->date('next_refill_date')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extinguisher_movements');
    }
};
