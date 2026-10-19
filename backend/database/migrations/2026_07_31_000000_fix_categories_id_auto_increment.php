<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $column = DB::selectOne(
            "SELECT EXTRA FROM information_schema.COLUMNS
              WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'categories' AND COLUMN_NAME = 'id'"
        );
        if ($column && stripos((string) $column->EXTRA, 'auto_increment') !== false) {
            return;
        }

        DB::statement('ALTER TABLE categories ADD COLUMN __row bigint unsigned NOT NULL AUTO_INCREMENT UNIQUE');

        DB::statement('DELETE c FROM categories c JOIN categories c2 ON c.id = c2.id AND c.__row > c2.__row');

        DB::statement('ALTER TABLE categories DROP COLUMN __row');

        // A drifted table can have lost its key entirely, and MySQL refuses
        // AUTO_INCREMENT on a column that is not a key — put the primary key
        // back before flipping the flag (id is unique and NOT NULL by now).
        $hasPk = DB::selectOne(
            "SELECT COUNT(*) AS c FROM information_schema.KEY_COLUMN_USAGE
              WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'categories'
                AND COLUMN_NAME = 'id' AND CONSTRAINT_NAME = 'PRIMARY'"
        );
        if ((int) $hasPk->c === 0) {
            DB::statement('ALTER TABLE categories ADD PRIMARY KEY (id)');
        }

        DB::statement('ALTER TABLE categories MODIFY id bigint(20) unsigned NOT NULL AUTO_INCREMENT');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE categories MODIFY id bigint(20) unsigned NOT NULL');
        DB::statement('ALTER TABLE categories DROP PRIMARY KEY');
    }
};
