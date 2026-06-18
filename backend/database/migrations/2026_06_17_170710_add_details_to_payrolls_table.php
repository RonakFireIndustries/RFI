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
        Schema::table('payrolls', function (Blueprint $table) {
            $table->foreignId('payroll_period_id')->nullable()->constrained('payroll_periods')->onDelete('cascade');
            $table->decimal('present_days', 5, 2)->default(0);
            $table->decimal('absent_days', 5, 2)->default(0);
            $table->decimal('paid_leaves', 5, 2)->default(0);
            $table->decimal('unpaid_leaves', 5, 2)->default(0);
            $table->decimal('working_days', 5, 2)->default(0);
            $table->decimal('conveyance', 10, 2)->default(0);
            $table->decimal('medical_allowance', 10, 2)->default(0);
            $table->decimal('special_allowance', 10, 2)->default(0);
            $table->decimal('site_allowance', 10, 2)->default(0);
            $table->decimal('travel_allowance', 10, 2)->default(0);
            $table->decimal('food_allowance', 10, 2)->default(0);
            $table->decimal('esic', 10, 2)->default(0);
            $table->decimal('tds', 10, 2)->default(0);
            $table->decimal('other_deductions', 10, 2)->default(0);
            $table->decimal('late_penalty', 10, 2)->default(0);
            $table->decimal('bonuses', 10, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropForeign(['payroll_period_id']);
            $table->dropColumn([
                'payroll_period_id', 'present_days', 'absent_days', 'paid_leaves', 'unpaid_leaves',
                'working_days', 'conveyance', 'medical_allowance', 'special_allowance', 'site_allowance',
                'travel_allowance', 'food_allowance', 'esic', 'tds', 'other_deductions', 'late_penalty', 'bonuses'
            ]);
        });
    }
};
