<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Voucher header — one row per double-entry voucher (Tally "Voucher").
     *
     * `voucher_type` holds a key from config/accounting.php. The header keeps
     * the total and, for party vouchers, a pointer to the party ledger so the
     * register and outstanding reports can find it without scanning entries.
     */
    public function up(): void
    {
        if (Schema::hasTable('vouchers')) {
            return;
        }

        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();

            $table->string('voucher_type');           // contra | payment | receipt | journal | ...
            $table->string('voucher_no');             // e.g. PMT-2026-0001
            $table->date('voucher_date');
            $table->foreignId('financial_year_id')->nullable()
                ->constrained('financial_years')->nullOnDelete();

            $table->string('reference_no')->nullable();   // instrument / cheque no.
            $table->text('narration')->nullable();

            $table->string('status')->default('posted');  // draft | posted | cancelled

            $table->foreignId('party_ledger_id')->nullable()
                ->constrained('ledger_accounts')->nullOnDelete();
            $table->decimal('amount', 15, 2)->default(0);  // total of the Dr (= Cr) side

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();

            $table->timestamps();

            $table->unique('voucher_no');
            $table->index(['voucher_type', 'voucher_date']);
            $table->index('voucher_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
