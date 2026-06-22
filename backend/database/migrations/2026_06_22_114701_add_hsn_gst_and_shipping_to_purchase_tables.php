<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('hsn_code')->nullable()->after('sku');
        });

        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->decimal('gst_rate', 5, 2)->default(0)->after('unit_cost');
            $table->string('hsn_code')->nullable()->after('gst_rate');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->decimal('shipping_cost', 10, 2)->default(0)->after('tax_amount');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('hsn_code');
        });

        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropColumn(['gst_rate', 'hsn_code']);
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn('shipping_cost');
        });
    }
};
