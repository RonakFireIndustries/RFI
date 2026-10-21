<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bill-level tax and discount for item vouchers.
 *
 * Tax and discount used to be keyed per line. Real bills apply both once, to
 * the whole document, so the header now carries the single tax rate and the
 * discount (either a percentage or a flat amount, per `discount_type`).
 *
 * `voucher_items` keeps its per-line tax / discount columns: lines written
 * before this change still hold their own figures, and each line is still
 * stamped with the bill's tax rate so downstream readers see a value.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('vouchers', 'tax_rate')) {
            Schema::table('vouchers', function (Blueprint $table) {
                $table->decimal('tax_rate', 5, 2)->default(0)->after('gst_type');
            });
        }
        if (! Schema::hasColumn('vouchers', 'discount_type')) {
            Schema::table('vouchers', function (Blueprint $table) {
                $table->string('discount_type', 10)->nullable()->after('tax_rate')
                    ->comment('percent | amount — how discount_value is read');
            });
        }
        if (! Schema::hasColumn('vouchers', 'discount_value')) {
            Schema::table('vouchers', function (Blueprint $table) {
                $table->decimal('discount_value', 15, 2)->default(0)->after('discount_type');
            });
        }

        // The line's unit is now the unit's display name, not its id/code, so
        // the 16-character column is too tight for names like "Square Metre".
        if (Schema::hasColumn('voucher_items', 'unit')) {
            Schema::table('voucher_items', function (Blueprint $table) {
                $table->string('unit', 50)->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            foreach (['tax_rate', 'discount_type', 'discount_value'] as $column) {
                if (Schema::hasColumn('vouchers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        if (Schema::hasColumn('voucher_items', 'unit')) {
            Schema::table('voucher_items', function (Blueprint $table) {
                $table->string('unit', 16)->nullable()->change();
            });
        }
    }
};
