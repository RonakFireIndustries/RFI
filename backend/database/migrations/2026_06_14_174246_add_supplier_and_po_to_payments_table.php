<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Make invoice_id nullable
            $table->unsignedBigInteger('invoice_id')->nullable()->change();

            // Add new fields
            $table->string('type')->default('Payable'); // Payable or Receivable
            $table->foreignId('supplier_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('purchase_order_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('sales_order_id')->nullable()->constrained()->onDelete('cascade');
            
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropForeign(['customer_id']);
            $table->dropForeign(['purchase_order_id']);
            $table->dropForeign(['sales_order_id']);

            $table->dropColumn([
                'type',
                'supplier_id',
                'customer_id',
                'purchase_order_id',
                'sales_order_id',
                'reference_number',
                'notes'
            ]);
        });
    }
};
