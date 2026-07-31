<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected array $tables = [
        'category_product',
        'customer_quotations',
        'customer_quotation_items',
        'delivery_notes',
        'delivery_note_items',
        'goods_receipt_notes',
        'grn_items',
        'inventories',
        'inventory_transactions',
        'inventory_transfers',
        'notes',
        'payments',
        'purchase_returns',
        'purchase_return_items',
        'sales_returns',
        'sales_return_items',
        'supplier_quotations',
        'supplier_quotation_items',
        'tasks',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            $duplicates = DB::select("SELECT id FROM `$table` GROUP BY id HAVING COUNT(*) > 1 LIMIT 1");

            if (!empty($duplicates)) {
                DB::statement("ALTER TABLE `$table` ADD COLUMN __row bigint unsigned NOT NULL AUTO_INCREMENT UNIQUE");
                DB::statement("DELETE c FROM `$table` c JOIN `$table` c2 ON c.id = c2.id AND c.__row > c2.__row");
                DB::statement("ALTER TABLE `$table` DROP COLUMN __row");
            }

            DB::statement("ALTER TABLE `$table` MODIFY id bigint(20) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY");
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            DB::statement("ALTER TABLE `$table` MODIFY id bigint(20) unsigned NOT NULL");
            DB::statement("ALTER TABLE `$table` DROP PRIMARY KEY");
        }
    }
};
