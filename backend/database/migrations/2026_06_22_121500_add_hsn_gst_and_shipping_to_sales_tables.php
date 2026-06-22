<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_order_items', function (Blueprint $table) {
            $table->decimal('gst_rate', 5, 2)->default(0)->after('unit_price');
            $table->string('hsn_code')->nullable()->after('gst_rate');
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->decimal('shipping_cost', 10, 2)->default(0)->after('tax_amount');
        });
    }

    public function down(): void
    {
        Schema::table('sales_order_items', function (Blueprint $table) {
            $table->dropColumn(['gst_rate', 'hsn_code']);
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn('shipping_cost');
        });
    }
};
