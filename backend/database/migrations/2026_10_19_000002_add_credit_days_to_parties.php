<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Party payment terms: credit days, the source of a bill's default due
     * date (voucher date + credit days, overridable per document).
     *
     * Nullable — a party with no terms behaves exactly as before and gets no
     * due date, so nothing becomes "overdue" by accident.
     */
    public function up(): void
    {
        foreach (['customers', 'suppliers'] as $table) {
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'credit_days')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->unsignedSmallInteger('credit_days')->nullable();
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['customers', 'suppliers'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'credit_days')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropColumn('credit_days');
                });
            }
        }
    }
};
