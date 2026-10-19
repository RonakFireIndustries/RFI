<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Manual bank reconciliation (slice 4).
     *
     * Each Dr/Cr line of a voucher can be marked as reconciled against the bank
     * statement, recording the statement date, who did it and an optional note
     * (cheque no. / UTR). Everything is nullable so the books are unaffected
     * until someone reconciles.
     */
    public function up(): void
    {
        if (Schema::hasColumn('voucher_entries', 'reconciled_at')) {
            return;
        }

        Schema::table('voucher_entries', function (Blueprint $table) {
            $table->date('reconciled_at')->nullable()->after('narration');
            $table->unsignedBigInteger('reconciled_by')->nullable()->after('reconciled_at');
            $table->string('reconciliation_note')->nullable()->after('reconciled_by');

            $table->index(['ledger_id', 'reconciled_at']);
        });
    }

    public function down(): void
    {
        Schema::table('voucher_entries', function (Blueprint $table) {
            $table->dropIndex(['ledger_id', 'reconciled_at']);
            $table->dropColumn(['reconciled_at', 'reconciled_by', 'reconciliation_note']);
        });
    }
};
