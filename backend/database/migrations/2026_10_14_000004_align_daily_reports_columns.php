<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bring `daily_reports` in line with the columns the application actually
     * reads and writes.
     *
     * The table has drifted from its migrations:
     *   - DailyReportService, DailyReportResource, DailyReport::getReports and
     *     DashboardService all use `date`, while the create migration defines
     *     `report_date`. Every dashboard DPR widget (my_dpr, dpr_reminder,
     *     dpr_summary, dpr_trend) blows up with "Unknown column 'report_date'"
     *     on any environment whose table uses the real name.
     *   - `work_description` is required by ValidationSchemas::dailyReport() and
     *     `materials_used` / `supervisor_remarks` / `status` are in the model's
     *     fillable, but no migration ever creates them - so a fresh install
     *     cannot accept a daily report at all.
     *
     * Every step is guarded, so an environment whose table already matches is
     * left completely untouched.
     */
    public function up(): void
    {
        if (!Schema::hasTable('daily_reports')) {
            return;
        }

        // `date` is the name the whole application uses; rename the migration's
        // `report_date` over to it rather than moving data around.
        if (!Schema::hasColumn('daily_reports', 'date')
            && Schema::hasColumn('daily_reports', 'report_date')) {
            Schema::table('daily_reports', function (Blueprint $table) {
                $table->renameColumn('report_date', 'date');
            });
        }

        Schema::table('daily_reports', function (Blueprint $table) {
            if (!Schema::hasColumn('daily_reports', 'date')) {
                $table->date('date')->nullable();
            }
            if (!Schema::hasColumn('daily_reports', 'work_description')) {
                $table->text('work_description')->nullable();
            }
            if (!Schema::hasColumn('daily_reports', 'materials_used')) {
                $table->text('materials_used')->nullable();
            }
            if (!Schema::hasColumn('daily_reports', 'supervisor_remarks')) {
                $table->text('supervisor_remarks')->nullable();
            }
            if (!Schema::hasColumn('daily_reports', 'status')) {
                $table->string('status')->nullable();
            }
        });
    }

    /**
     * Deliberately not reversible. Renaming `date` back to `report_date` would
     * re-break every DPR query, and dropping the added columns could discard
     * data written since this migration ran.
     */
    public function down(): void
    {
        //
    }
};
