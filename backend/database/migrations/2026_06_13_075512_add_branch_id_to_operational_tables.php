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
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->constrained('branches')->cascadeOnDelete();
            }
            if (!Schema::hasColumn('employees', 'designation')) {
                $table->string('designation')->nullable();
            }
            if (!Schema::hasColumn('employees', 'reporting_manager_id')) {
                $table->foreignId('reporting_manager_id')->nullable()->constrained('users')->nullOnDelete();
            }
        });

        $tables = [
            'invoices', 'payments', 'payrolls', 'tasks',
            'attendances', 'customers', 'suppliers'
        ];

        foreach ($tables as $tableName) {
            if (!Schema::hasColumn($tableName, 'branch_id')) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    $table->foreignId('branch_id')->nullable()->constrained('branches')->cascadeOnDelete();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operational_tables', function (Blueprint $table) {
            //
        });
    }
};
