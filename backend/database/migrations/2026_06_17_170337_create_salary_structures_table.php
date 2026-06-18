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
        Schema::create('salary_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->decimal('basic_salary', 10, 2)->default(0);
            $table->decimal('hra', 10, 2)->default(0);
            $table->decimal('conveyance', 10, 2)->default(0);
            $table->decimal('medical_allowance', 10, 2)->default(0);
            $table->decimal('special_allowance', 10, 2)->default(0);
            $table->decimal('site_allowance', 10, 2)->default(0);
            $table->decimal('travel_allowance', 10, 2)->default(0);
            $table->decimal('food_allowance', 10, 2)->default(0);
            $table->decimal('other_earnings', 10, 2)->default(0);
            $table->decimal('pf_deduction', 10, 2)->default(0);
            $table->decimal('esic_deduction', 10, 2)->default(0);
            $table->decimal('professional_tax', 10, 2)->default(0);
            $table->decimal('tds', 10, 2)->default(0);
            $table->decimal('other_deductions', 10, 2)->default(0);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_structures');
    }
};
