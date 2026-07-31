<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE categories ADD COLUMN __row bigint unsigned NOT NULL AUTO_INCREMENT UNIQUE');

        DB::statement('DELETE c FROM categories c JOIN categories c2 ON c.id = c2.id AND c.__row > c2.__row');

        DB::statement('ALTER TABLE categories DROP COLUMN __row');

        DB::statement('ALTER TABLE categories MODIFY id bigint(20) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE categories MODIFY id bigint(20) unsigned NOT NULL');
        DB::statement('ALTER TABLE categories DROP PRIMARY KEY');
    }
};
