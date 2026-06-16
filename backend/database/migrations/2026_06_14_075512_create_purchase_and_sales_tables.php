<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Supplier Quotations
        Schema::create('supplier_quotations', function (Blueprint $table) {
            $table->id();
            $table->string('quotation_number')->unique();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->string('status')->default('Draft'); // Draft, Sent, Approved, Rejected, Converted
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('supplier_quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_quotation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->timestamps();
        });

        // 2. Modify Purchase Orders
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->string('po_number')->unique()->after('id');
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete()->after('branch_id');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete()->after('requested_by');
            $table->text('notes')->nullable()->after('status');
            $table->decimal('tax_amount', 12, 2)->default(0)->after('total_amount');
            // Status gets expanded: Draft, Pending Approval, Approved, Rejected, Partially Received, Fully Received, Closed
        });

        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity');
            $table->integer('received_quantity')->default(0);
            $table->decimal('unit_cost', 10, 2);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->timestamps();
        });

        // 3. Goods Receipt Notes (GRN)
        Schema::create('goods_receipt_notes', function (Blueprint $table) {
            $table->id();
            $table->string('grn_number')->unique();
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('receipt_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('grn_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_receipt_note_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_order_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('received_quantity');
            $table->timestamps();
        });

        // 4. Purchase Returns
        Schema::create('purchase_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_number')->unique();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('goods_receipt_note_id')->nullable()->constrained()->nullOnDelete();
            $table->string('supplier_reference')->nullable();
            $table->date('return_date');
            $table->text('return_reason')->nullable();
            $table->string('status')->default('Draft'); // Draft, Approved, Processed
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('purchase_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_return_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('return_quantity');
            $table->timestamps();
        });


        // 5. Customer Quotations
        Schema::create('customer_quotations', function (Blueprint $table) {
            $table->id();
            $table->string('quotation_number')->unique();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->string('status')->default('Draft'); // Draft, Sent, Approved, Rejected, Converted
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('customer_quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_quotation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->timestamps();
        });

        // 6. Modify Sales Orders
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->string('so_number')->unique()->after('id');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->after('branch_id');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete()->after('created_by');
            $table->text('notes')->nullable()->after('status');
            $table->decimal('tax_amount', 12, 2)->default(0)->after('total_amount');
            $table->decimal('discount_amount', 12, 2)->default(0)->after('tax_amount');
            // Status gets expanded: Draft, Pending Approval, Approved, Rejected, Partially Delivered, Fully Delivered, Invoiced, Closed
        });

        Schema::table('sales_order_items', function (Blueprint $table) {
            $table->integer('delivered_quantity')->default(0)->after('quantity');
            $table->decimal('discount', 10, 2)->default(0)->after('gst_amount');
            $table->decimal('total', 12, 2)->default(0)->after('discount');
        });

        // 7. Delivery Notes
        Schema::create('delivery_notes', function (Blueprint $table) {
            $table->id();
            $table->string('delivery_number')->unique();
            $table->foreignId('sales_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('delivered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('delivery_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('delivery_note_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_note_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sales_order_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('delivered_quantity');
            $table->timestamps();
        });

        // 8. Sales Returns
        Schema::create('sales_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_number')->unique();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_reference')->nullable();
            $table->date('return_date');
            $table->text('return_reason')->nullable();
            $table->string('status')->default('Draft'); // Draft, Approved, Processed
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('sales_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_return_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('return_quantity');
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('sales_return_items');
        Schema::dropIfExists('sales_returns');
        Schema::dropIfExists('delivery_note_items');
        Schema::dropIfExists('delivery_notes');
        Schema::dropIfExists('customer_quotation_items');
        Schema::dropIfExists('customer_quotations');
        
        Schema::dropIfExists('purchase_return_items');
        Schema::dropIfExists('purchase_returns');
        Schema::dropIfExists('grn_items');
        Schema::dropIfExists('goods_receipt_notes');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('supplier_quotation_items');
        Schema::dropIfExists('supplier_quotations');

        Schema::table('sales_order_items', function (Blueprint $table) {
            $table->dropColumn(['delivered_quantity', 'discount', 'total']);
        });
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['so_number', 'created_by', 'approved_by', 'notes', 'tax_amount', 'discount_amount']);
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['requested_by']);
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['po_number', 'requested_by', 'approved_by', 'notes', 'tax_amount']);
        });
    }
};
