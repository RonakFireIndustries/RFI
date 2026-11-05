<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (! Schema::hasColumn('sales_order_items', 'base_quantity')) {
            Schema::table('sales_order_items', function (Blueprint $table) {
                $table->decimal('base_quantity', 15, 4)->default(0)->after('unit_id');
            });
        }
    }

    public function down(): void {
        Schema::table('sales_order_items', function (Blueprint $table) {
            $table->dropColumn('base_quantity');
        });
    }
};