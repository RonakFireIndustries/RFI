<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (! Schema::hasColumn('sales_order_items', 'unit_id')) {
            Schema::table('sales_order_items', function (Blueprint $table) {
                $table->unsignedInteger('unit_id')->nullable()->after('quantity');
            });
        }
    }

    public function down(): void {
        Schema::table('sales_order_items', function (Blueprint $table) {
            $table->dropColumn('unit_id');
        });
    }
};