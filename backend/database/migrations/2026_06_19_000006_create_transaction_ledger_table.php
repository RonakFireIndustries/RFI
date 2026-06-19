<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('transaction_ledger')) {
            Schema::create('transaction_ledger', function (Blueprint $table) {
                $table->id();
                $table->string('transaction_number')->unique();
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('location_id');
                $table->unsignedBigInteger('to_location_id')->nullable();
                $table->enum('transaction_type', [
                    'opening_stock', 'purchase', 'purchase_return', 'issue',
                    'consumption', 'transfer_in', 'transfer_out', 'adjustment',
                    'damage', 'sales', 'sales_return'
                ]);
                $table->decimal('quantity', 12, 2);
                $table->decimal('unit_price', 12, 2)->default(0);
                $table->decimal('total_price', 14, 2)->default(0);
                $table->decimal('quantity_before', 12, 2)->default(0);
                $table->decimal('quantity_after', 12, 2)->default(0);
                $table->string('reference_type')->nullable();
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
            });

            try {
                Schema::table('transaction_ledger', function (Blueprint $table) {
                    $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
                    $table->foreign('location_id')->references('id')->on('inventory_locations')->cascadeOnDelete();
                    $table->foreign('to_location_id')->references('id')->on('inventory_locations')->nullOnDelete();
                    $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
                });
            } catch (\Exception $e) {
                // FK constraints optional if engine mismatch
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_ledger');
    }
};
