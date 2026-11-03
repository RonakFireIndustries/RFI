<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Remember the unit a quantity was entered in, next to the base quantity that
 * was actually posted to stock.
 *
 * Stock is always kept in the product's own unit (`products.unit_id`). These
 * columns are the audit trail for documents that accept another unit: what the
 * user keyed, and which unit it was keyed in. Existing rows stay null and are
 * read as already being in the product's base unit.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('transaction_ledger') && ! Schema::hasColumn('transaction_ledger', 'unit_id')) {
            Schema::table('transaction_ledger', function (Blueprint $table) {
                $table->unsignedBigInteger('unit_id')->nullable()->after('product_id');
                $table->decimal('entered_quantity', 15, 3)->nullable()->after('quantity');
            });
        }

        if (Schema::hasTable('stock_requests') && ! Schema::hasColumn('stock_requests', 'unit_id')) {
            Schema::table('stock_requests', function (Blueprint $table) {
                $table->unsignedBigInteger('unit_id')->nullable()->after('product_id');
            });
        }

        if (Schema::hasTable('voucher_items') && ! Schema::hasColumn('voucher_items', 'unit_id')) {
            Schema::table('voucher_items', function (Blueprint $table) {
                $table->unsignedBigInteger('unit_id')->nullable()->after('product_id');
            });
        }

        if (Schema::hasTable('stock_receipt_items') && ! Schema::hasColumn('stock_receipt_items', 'unit_id')) {
            Schema::table('stock_receipt_items', function (Blueprint $table) {
                $table->unsignedBigInteger('unit_id')->nullable()->after('product_id');
                $table->decimal('base_quantity', 15, 3)->nullable()->after('quantity');
            });
        }

        if (Schema::hasTable('quotation_items') && ! Schema::hasColumn('quotation_items', 'unit_id')) {
            Schema::table('quotation_items', function (Blueprint $table) {
                $table->unsignedBigInteger('unit_id')->nullable()->after('product_id');
                $table->decimal('base_qty', 15, 3)->nullable()->after('qty');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('transaction_ledger', 'unit_id')) {
            Schema::table('transaction_ledger', function (Blueprint $table) {
                $table->dropColumn(['unit_id', 'entered_quantity']);
            });
        }

        if (Schema::hasColumn('stock_requests', 'unit_id')) {
            Schema::table('stock_requests', function (Blueprint $table) {
                $table->dropColumn('unit_id');
            });
        }

        if (Schema::hasColumn('voucher_items', 'unit_id')) {
            Schema::table('voucher_items', function (Blueprint $table) {
                $table->dropColumn('unit_id');
            });
        }

        if (Schema::hasColumn('stock_receipt_items', 'unit_id')) {
            Schema::table('stock_receipt_items', function (Blueprint $table) {
                $table->dropColumn(['unit_id', 'base_quantity']);
            });
        }

        if (Schema::hasColumn('quotation_items', 'unit_id')) {
            Schema::table('quotation_items', function (Blueprint $table) {
                $table->dropColumn(['unit_id', 'base_qty']);
            });
        }
    }
};
