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
            $table->foreignId('branch_id')->nullable()->constrained('branches')->cascadeOnDelete();
            $table->string('designation')->nullable();
            $table->foreignId('reporting_manager_id')->nullable()->constrained('users')->nullOnDelete();
        });

        $tables = [
            'invoices', 'payments', 'payrolls', 'tasks', 
            'attendances', 'customers', 'suppliers'
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('branch_id')->nullable()->constrained('branches')->cascadeOnDelete();
            });
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
