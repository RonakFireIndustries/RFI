<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->decimal('estimated_value', 12, 2)->nullable();
            $table->integer('probability')->default(50);
            $table->string('stage')->default('Prospect');
            $table->date('expected_closing_date')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('actual_final_value', 12, 2)->nullable();
            $table->date('project_start_date')->nullable();
            $table->string('lost_reason')->nullable();
            $table->string('status')->default('Active');
            $table->timestamps();
        });

        Schema::create('opportunity_work_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opportunity_id')->constrained()->cascadeOnDelete();
            $table->string('work_type');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunity_work_types');
        Schema::dropIfExists('opportunities');
    }
};
