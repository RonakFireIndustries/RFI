<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('daily_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('site_id')->nullable()->constrained()->onDelete('set null');
            $table->date('date');
            $table->text('work_description')->nullable();
            $table->text('materials_used')->nullable();
            $table->text('supervisor_remarks')->nullable();
            $table->string('status')->default('Submitted'); // Submitted, Approved, Rejected
            $table->string('photo_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_reports');
    }
};
