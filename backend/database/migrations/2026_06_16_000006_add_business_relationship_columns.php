<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('employees')) {
            Schema::table('employees', function (Blueprint $table) {
                if (!Schema::hasColumn('employees', 'department_id')) {
                    $table->foreignId('department_id')->nullable()->after('department')->constrained('departments')->nullOnDelete();
                }

                if (!Schema::hasColumn('employees', 'designation_id')) {
                    $table->foreignId('designation_id')->nullable()->after('department_id')->constrained('designations')->nullOnDelete();
                }
            });
        }

        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (!Schema::hasColumn('products', 'supplier_id')) {
                    $table->foreignId('supplier_id')->nullable()->after('category_id')->constrained('suppliers')->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'supplier_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropConstrainedForeignId('supplier_id');
            });
        }

        if (Schema::hasTable('employees')) {
            Schema::table('employees', function (Blueprint $table) {
                if (Schema::hasColumn('employees', 'designation_id')) {
                    $table->dropConstrainedForeignId('designation_id');
                }

                if (Schema::hasColumn('employees', 'department_id')) {
                    $table->dropConstrainedForeignId('department_id');
                }
            });
        }
    }
};
