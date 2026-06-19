<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $keys = DB::select('SHOW KEYS FROM suppliers WHERE Key_name = ?', ['PRIMARY']);
        if (empty($keys)) {
            DB::statement('ALTER TABLE suppliers ADD PRIMARY KEY (id)');
        }

        $result = DB::select('SHOW CREATE TABLE suppliers')[0]->{'Create Table'};
        if (!str_contains($result, 'AUTO_INCREMENT')) {
            DB::statement('ALTER TABLE suppliers MODIFY id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT');
        }
    }

    public function down(): void
    {
        // Cannot safely reverse
    }
};
