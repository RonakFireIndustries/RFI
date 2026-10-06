<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['sales_orders', 'sales_order_items', 'invoice_items'] as $table) {
            DB::statement("ALTER TABLE `{$table}` MODIFY COLUMN id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT");

            $hasPk = DB::selectOne(
                "SELECT COUNT(*) AS c FROM information_schema.TABLE_CONSTRAINTS
                  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_TYPE = 'PRIMARY KEY'",
                [$table]
            );
            if ((int) $hasPk->c === 0) {
                DB::statement("ALTER TABLE `{$table}` ADD PRIMARY KEY (id)");
            }
        }
    }

    public function down(): void
    {
        // Not reversible
    }
};
