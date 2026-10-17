<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The Dr/Cr lines of a voucher. A voucher is balanced when the two sides
     * total the same; the sum of `amount` across both sides equals twice the
     * voucher total.
     */
    public function up(): void
    {
        if (Schema::hasTable('voucher_entries')) {
            return;
        }

        Schema::create('voucher_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('line_no')->default(1);
            $table->foreignId('ledger_id')->constrained('ledger_accounts')->restrictOnDelete();
            $table->enum('entry_type', ['Dr', 'Cr']);
            $table->decimal('amount', 15, 2);
            $table->string('narration')->nullable();
            $table->timestamps();

            $table->index('ledger_id');
            $table->index(['voucher_id', 'line_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher_entries');
    }
};
