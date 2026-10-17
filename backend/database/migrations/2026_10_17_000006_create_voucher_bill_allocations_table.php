<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bill-wise details attached to the party line of a voucher (Tally's
     * "Bill-wise Details"). Each row says how much of the voucher settles a
     * particular bill — an existing invoice / purchase order, the party's
     * opening balance, a manually typed reference, or an advance.
     *
     * `bill_id` is a soft reference (no FK) because a bill may live in any of
     * several tables over time: the legacy invoices / purchase_orders today,
     * sales / purchase vouchers in a later slice.
     */
    public function up(): void
    {
        if (Schema::hasTable('voucher_bill_allocations')) {
            return;
        }

        Schema::create('voucher_bill_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ledger_id')->constrained('ledger_accounts')->cascadeOnDelete();

            $table->string('bill_type');                  // opening | invoice | purchase-order | ...
            $table->unsignedBigInteger('bill_id')->nullable();
            $table->string('bill_name')->nullable();      // display / manual reference

            $table->decimal('amount', 15, 2);
            $table->timestamps();

            $table->index(['ledger_id', 'bill_type', 'bill_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher_bill_allocations');
    }
};
