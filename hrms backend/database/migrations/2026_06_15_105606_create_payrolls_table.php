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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->integer('month');
            $table->integer('year');
            
            // Earnings
            $table->decimal('basic_salary', 10, 2)->default(0);
            $table->decimal('hra', 10, 2)->default(0);
            $table->decimal('other_allowance', 10, 2)->default(0);
            $table->decimal('overtime_pay', 10, 2)->default(0);
            
            // Deductions
            $table->decimal('pf', 10, 2)->default(0);
            $table->decimal('pt', 10, 2)->default(0);
            $table->decimal('salary_advance', 10, 2)->default(0);
            $table->decimal('loss_of_pay', 10, 2)->default(0);
            
            $table->decimal('net_salary', 10, 2)->default(0);
            $table->string('status')->default('Draft'); // Draft, Processed, Paid
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
