<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $branchIdTables = [
        'attendances', 'categories', 'invoices', 'inventories', 'shifts',
        'payrolls', 'purchase_orders', 'sales_orders', 'delivery_notes',
        'goods_receipt_notes', 'payments', 'suppliers', 'customers', 'tasks',
    ];

    public function up(): void
    {
        // Drop foreign keys then columns (using raw DB to avoid Doctrine issues)
        foreach ($this->branchIdTables as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'branch_id')) continue;
            try { DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY IF EXISTS `{$table}_branch_id_foreign`"); } catch (\Exception) {}
            Schema::table($table, fn (Blueprint $t) => $t->dropColumn('branch_id'));
        }

        // inventory_transfers has source_branch_id and destination_branch_id
        if (Schema::hasTable('inventory_transfers')) {
            foreach (['source_branch_id', 'destination_branch_id'] as $col) {
                if (!Schema::hasColumn('inventory_transfers', $col)) continue;
                try { DB::statement("ALTER TABLE `inventory_transfers` DROP FOREIGN KEY IF EXISTS `inventory_transfers_{$col}_foreign`"); } catch (\Exception) {}
                Schema::table('inventory_transfers', fn (Blueprint $t) => $t->dropColumn($col));
            }
        }

        // Drop branches table
        Schema::dropIfExists('branches');
    }

    public function down(): void
    {
        // Recreate branches table
        if (!Schema::hasTable('branches')) {
            Schema::create('branches', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('location')->nullable();
                $table->timestamps();
            });
        }
    }
};
