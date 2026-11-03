<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The billed quantity stays in the unit the line was keyed in; this is the
 * same figure converted into the product's own unit, matching what
 * quotation_items.base_qty already stores. Null on rows written before unit
 * tracking, and on lines with no product to convert against.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('voucher_items') && ! Schema::hasColumn('voucher_items', 'base_quantity')) {
            Schema::table('voucher_items', function (Blueprint $table) {
                $table->decimal('base_quantity', 15, 3)->nullable()->after('quantity');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('voucher_items') && Schema::hasColumn('voucher_items', 'base_quantity')) {
            Schema::table('voucher_items', function (Blueprint $table) {
                $table->dropColumn('base_quantity');
            });
        }
    }
};
