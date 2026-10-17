<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ledger accounts — the unit every voucher posts to (Tally "Ledgers").
     *
     * `opening_balance` + `opening_type` hold the balance brought forward into
     * the active financial year, in Tally's Dr/Cr style: Cash and expense
     * ledgers usually open Dr, income and liability ledgers open Cr.
     */
    public function up(): void
    {
        if (Schema::hasTable('ledger_accounts')) {
            return;
        }

        Schema::create('ledger_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->foreignId('group_id')->constrained('account_groups')->restrictOnDelete();

            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->enum('opening_type', ['Dr', 'Cr'])->default('Dr');

            // Bank / cash ledgers drive Contra vouchers and Bank Reconciliation.
            $table->boolean('is_bank')->default(false);
            $table->boolean('is_cash')->default(false);
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_ifsc')->nullable();
            $table->string('bank_branch')->nullable();

            // Tax details, kept on the ledger just like a Tally GST ledger.
            $table->string('gst_number')->nullable();
            $table->string('gst_type')->nullable(); // Regular | Composition | Unregistered
            $table->string('pan_number')->nullable();

            // Party ledgers (Sundry Debtors / Sundry Creditors) link back to the
            // customer or supplier master they represent.
            $table->string('party_type')->nullable(); // customer | supplier
            $table->unsignedBigInteger('party_id')->nullable();

            $table->boolean('is_system')->default(false);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['party_type', 'party_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_accounts');
    }
};
