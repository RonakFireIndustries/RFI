<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE products MODIFY id bigint(20) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE products MODIFY id bigint(20) unsigned NOT NULL');
        DB::statement('ALTER TABLE products DROP PRIMARY KEY');
    }
};
