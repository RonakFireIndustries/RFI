<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_stock', function ($table) {
            $table->dropUnique(['product_id', 'location_id']);
            $table->unique(['product_id', 'location_type', 'location_id']);
        });
    }

    public function down(): void
    {
        Schema::table('product_stock', function ($table) {
            $table->dropUnique(['product_id', 'location_type', 'location_id']);
            $table->unique(['product_id', 'location_id']);
        });
    }
};
