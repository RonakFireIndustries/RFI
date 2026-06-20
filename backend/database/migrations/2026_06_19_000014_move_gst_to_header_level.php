<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('gst_percentage');
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn(['tax_rate', 'cgst_amount', 'sgst_amount', 'igst_amount']);
        });

        Schema::table('sales_order_items', function (Blueprint $table) {
            $table->dropColumn('gst_amount');
        });

        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropColumn('tax');
        });

        Schema::table('supplier_quotation_items', function (Blueprint $table) {
            $table->dropColumn('tax');
        });

        Schema::table('customer_quotation_items', function (Blueprint $table) {
            $table->dropColumn('tax');
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->string('gst_type')->nullable()->after('tax_amount');
            $table->decimal('gst_rate', 5, 2)->default(0)->after('gst_type');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->string('gst_type')->nullable()->after('tax_amount');
            $table->decimal('gst_rate', 5, 2)->default(0)->after('gst_type');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->string('gst_type')->nullable()->after('igst_total');
            $table->decimal('gst_rate', 5, 2)->default(0)->after('gst_type');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('gst_percentage', 5, 2)->default(0)->after('selling_price');
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->decimal('tax_rate', 5, 2)->default(0)->after('discount');
            $table->decimal('cgst_amount', 15, 2)->default(0)->after('tax_rate');
            $table->decimal('sgst_amount', 15, 2)->default(0)->after('cgst_amount');
            $table->decimal('igst_amount', 15, 2)->default(0)->after('sgst_amount');
        });

        Schema::table('sales_order_items', function (Blueprint $table) {
            $table->decimal('gst_amount', 10, 2)->default(0)->after('unit_price');
        });

        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->decimal('tax', 10, 2)->default(0)->after('unit_cost');
        });

        Schema::table('supplier_quotation_items', function (Blueprint $table) {
            $table->decimal('tax', 10, 2)->default(0)->after('unit_price');
        });

        Schema::table('customer_quotation_items', function (Blueprint $table) {
            $table->decimal('tax', 10, 2)->default(0)->after('unit_price');
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn(['gst_type', 'gst_rate']);
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['gst_type', 'gst_rate']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['gst_type', 'gst_rate']);
        });
    }
};
