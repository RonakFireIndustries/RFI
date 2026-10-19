<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('voucher_items')) {
            return;
        }

        Schema::create('voucher_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('line_no')->default(1);

            $table->unsignedBigInteger('product_id')->nullable()->index();
            $table->string('item_description');
            $table->string('hsn_code')->nullable();
            $table->string('unit')->nullable();

            $table->decimal('quantity', 15, 3)->default(0);
            $table->decimal('rate', 15, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('taxable_amount', 15, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('cgst_amount', 15, 2)->default(0);
            $table->decimal('sgst_amount', 15, 2)->default(0);
            $table->decimal('igst_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);

            $table->timestamps();

            $table->index(['voucher_id', 'line_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher_items');
    }
};