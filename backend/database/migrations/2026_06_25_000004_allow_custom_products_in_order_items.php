<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function makeProductIdNullable(string $table): void
    {
        $fkName = DB::selectOne("
            SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?
              AND COLUMN_NAME = 'product_id' AND REFERENCED_TABLE_NAME IS NOT NULL
        ", [$table]);

        Schema::disableForeignKeyConstraints();
        if ($fkName) {
            DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$fkName->CONSTRAINT_NAME}`");
        }
        DB::statement("ALTER TABLE `{$table}` MODIFY `product_id` BIGINT UNSIGNED NULL");
        DB::statement("ALTER TABLE `{$table}` ADD CONSTRAINT `{$table}_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE");
        Schema::enableForeignKeyConstraints();

        if (!Schema::hasColumn($table, 'custom_product_name')) {
            DB::statement("ALTER TABLE `{$table}` ADD `custom_product_name` VARCHAR(255) NULL AFTER `product_id`");
        }
    }

    public function up(): void
    {
        $this->makeProductIdNullable('purchase_order_items');
        $this->makeProductIdNullable('sales_order_items');
    }

    public function down(): void
    {
        $tables = ['purchase_order_items', 'sales_order_items'];
        foreach ($tables as $table) {
            Schema::disableForeignKeyConstraints();
            $fkName = DB::selectOne("
                SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?
                  AND COLUMN_NAME = 'product_id' AND REFERENCED_TABLE_NAME IS NOT NULL
            ", [$table]);
            if ($fkName) {
                DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$fkName->CONSTRAINT_NAME}`");
            }
            DB::statement("ALTER TABLE `{$table}` DROP COLUMN `custom_product_name`");
            DB::statement("ALTER TABLE `{$table}` MODIFY `product_id` BIGINT UNSIGNED NOT NULL");
            DB::statement("ALTER TABLE `{$table}` ADD CONSTRAINT `{$table}_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE");
            Schema::enableForeignKeyConstraints();
        }
    }
};
