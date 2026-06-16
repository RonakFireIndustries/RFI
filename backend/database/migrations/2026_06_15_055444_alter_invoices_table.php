<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('cascade');
            
            // Allow sales_order_id to be nullable if it wasn't already (in my view_file it was NOT nullable)
            // But we can't easily make it nullable if there's an existing constraint without dropping it.
            // For now, let's just add new columns.
            
            // Amounts
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('cgst_total', 15, 2)->default(0);
            $table->decimal('sgst_total', 15, 2)->default(0);
            $table->decimal('igst_total', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            
            $table->string('status')->default('Draft'); // Draft, Sent, Partially Paid, Paid, Unpaid, Overdue, Cancelled
            $table->date('payment_date')->nullable();
            $table->decimal('paid_amount', 15, 2)->default(0);
            
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
            
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropForeign(['created_by']);
            $table->dropColumn([
                'customer_id', 'subtotal', 'cgst_total', 'sgst_total', 
                'igst_total', 'grand_total', 'status', 'payment_date', 'paid_amount', 
                'notes', 'terms', 'created_by'
            ]);
        });
    }
};
