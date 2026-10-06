<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE purchase_order_items MODIFY COLUMN id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT');

        $hasPk = DB::selectOne(
            "SELECT COUNT(*) AS c FROM information_schema.TABLE_CONSTRAINTS
              WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'purchase_order_items' AND CONSTRAINT_TYPE = 'PRIMARY KEY'"
        );
        if ((int) $hasPk->c === 0) {
            DB::statement('ALTER TABLE purchase_order_items ADD PRIMARY KEY (id)');
        }
    }

    public function down(): void
    {
        // Not reversible
    }
};
