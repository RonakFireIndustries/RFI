<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Petty-cash vouchers. A voucher records cash handed out for a small
     * expense and then moves Draft -> Approved -> Paid (or Rejected).
     */
    public function up(): void
    {
        if (Schema::hasTable('cash_vouchers')) {
            return;
        }

        Schema::create('cash_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_no')->unique();
            $table->date('voucher_date');
            $table->string('paid_to');
            $table->text('purpose')->nullable();
            $table->string('category')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('payment_mode')->default('Cash');
            $table->string('reference_no')->nullable();

            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();

            $table->string('attachment_path')->nullable();
            $table->string('status')->default('Draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_vouchers');
    }
};
