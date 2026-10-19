<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('vouchers', 'taxable_total')) {
            Schema::table('vouchers', function (Blueprint $table) {
                $table->decimal('taxable_total', 15, 2)->default(0)->after('amount');
            });
        }
        if (! Schema::hasColumn('vouchers', 'discount_total')) {
            Schema::table('vouchers', function (Blueprint $table) {
                $table->decimal('discount_total', 15, 2)->default(0)->after('taxable_total');
            });
        }
        if (! Schema::hasColumn('vouchers', 'tax_total')) {
            Schema::table('vouchers', function (Blueprint $table) {
                $table->decimal('tax_total', 15, 2)->default(0)->after('discount_total');
            });
        }
        if (! Schema::hasColumn('vouchers', 'round_off')) {
            Schema::table('vouchers', function (Blueprint $table) {
                $table->decimal('round_off', 15, 2)->default(0)->after('tax_total');
            });
        }
        if (! Schema::hasColumn('vouchers', 'gst_type')) {
            Schema::table('vouchers', function (Blueprint $table) {
                $table->string('gst_type')->nullable()->after('round_off');
            });
        }
    }

    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $columns = ['taxable_total', 'discount_total', 'tax_total', 'round_off', 'gst_type'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('vouchers', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};