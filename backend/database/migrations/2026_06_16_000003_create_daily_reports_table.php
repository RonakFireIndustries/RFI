<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('daily_reports')) {
            Schema::create('daily_reports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
                $table->unsignedBigInteger('site_id')->nullable();
                $table->date('report_date');
                $table->text('content')->nullable();
                $table->timestamps();

                $table->foreign('site_id')->references('id')->on('sites')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_reports');
    }
};
