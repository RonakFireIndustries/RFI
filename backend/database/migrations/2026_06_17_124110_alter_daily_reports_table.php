<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_reports', function (Blueprint $table) {
            if (!Schema::hasColumn('daily_reports', 'tasks_completed')) {
                $table->text('tasks_completed')->nullable();
            }
            if (!Schema::hasColumn('daily_reports', 'hours_worked')) {
                $table->decimal('hours_worked', 5, 2)->default(0);
            }
            if (!Schema::hasColumn('daily_reports', 'issues_faced')) {
                $table->text('issues_faced')->nullable();
            }
            if (!Schema::hasColumn('daily_reports', 'equipment_used')) {
                $table->text('equipment_used')->nullable();
            }
            if (!Schema::hasColumn('daily_reports', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable();
            }
            if (!Schema::hasColumn('daily_reports', 'approved_at')) {
                $table->timestamp('approved_at')->nullable();
            }
            if (!Schema::hasColumn('daily_reports', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('daily_reports', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'tasks_completed',
                'hours_worked',
                'issues_faced',
                'equipment_used',
                'submitted_at',
                'approved_at',
                'approved_by'
            ]);
        });
    }
};
