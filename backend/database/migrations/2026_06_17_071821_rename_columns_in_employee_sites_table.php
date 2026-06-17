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
        Schema::table('employee_sites', function (Blueprint $table) {
            if (Schema::hasColumn('employee_sites', 'allocation_date') && !Schema::hasColumn('employee_sites', 'assigned_at')) {
                $table->renameColumn('allocation_date', 'assigned_at');
            }
            if (Schema::hasColumn('employee_sites', 'status') && !Schema::hasColumn('employee_sites', 'role')) {
                $table->renameColumn('status', 'role');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_sites', function (Blueprint $table) {
            if (Schema::hasColumn('employee_sites', 'assigned_at') && !Schema::hasColumn('employee_sites', 'allocation_date')) {
                $table->renameColumn('assigned_at', 'allocation_date');
            }
            if (Schema::hasColumn('employee_sites', 'role') && !Schema::hasColumn('employee_sites', 'status')) {
                $table->renameColumn('role', 'status');
            }
        });
    }
};
