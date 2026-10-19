<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A bill's due date, so overdue can fire for new documents too.
     *
     * Today only legacy invoices carry one (`invoices.due_date`), which means
     * vouchers and purchase orders can never be flagged overdue — they age
     * from their document date instead. This adds the same column to the
     * voucher header; the value defaults to the party's `credit_days` added to
     * the voucher date and can be overridden per document on entry.
     */
    public function up(): void
    {
        // hasTable too: tests hand-run migration lists, so the guard must not
        // assume this file lands after the vouchers create.
        if (! Schema::hasTable('vouchers') || Schema::hasColumn('vouchers', 'due_date')) {
            return;
        }

        Schema::table('vouchers', function (Blueprint $table) {
            $table->date('due_date')->nullable();
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('vouchers', 'due_date')) {
            Schema::table('vouchers', function (Blueprint $table) {
                $table->dropColumn('due_date');
            });
        }
    }
};
