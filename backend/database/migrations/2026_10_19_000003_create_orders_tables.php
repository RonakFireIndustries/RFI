<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Order tracking: orders, their lines and the status timeline.
     *
     * Orders are operational only — there is no amount here that ever reaches
     * a ledger. `document_type` / `document_id` point at the invoice or voucher
     * an order became (reusing the accounting bill-type vocabulary so the
     * payment panel can match a bill in PartyOutstandingService), and the
     * totals exist purely for display.
     */
    public function up(): void
    {
        if (! Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->id();
                // SO-2026-0001 / PO-2026-0001 — per financial year, per side.
                $table->string('order_no')->unique();
                $table->string('side')->default('sales'); // sales | purchase
                $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

                $table->date('order_date');
                $table->date('expected_delivery_date')->nullable();

                // open | in_progress | fulfilled | invoiced | done | cancelled
                $table->string('status')->default('open');
                $table->string('reference_no')->nullable();
                $table->text('notes')->nullable();

                // The bill this order became; null until status = invoiced.
                $table->string('document_type')->nullable();
                $table->unsignedBigInteger('document_id')->nullable();

                // Display-only totals (qty × rate − discount + tax).
                $table->decimal('subtotal', 15, 2)->default(0);
                $table->decimal('discount_total', 15, 2)->default(0);
                $table->decimal('tax_total', 15, 2)->default(0);
                $table->decimal('total_amount', 15, 2)->default(0);

                $table->timestamps();

                $table->index(['side', 'status']);
                $table->index(['customer_id', 'status']);
                $table->index(['supplier_id', 'status']);
                $table->index(['document_type', 'document_id']);
            });
        }

        if (! Schema::hasTable('order_items')) {
            Schema::create('order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
                // Soft reference: a custom line has no product at all.
                $table->unsignedBigInteger('product_id')->nullable();
                $table->string('item_description');
                $table->string('hsn_code')->nullable();
                $table->string('unit')->nullable();
                $table->decimal('quantity', 12, 3);
                $table->decimal('rate', 15, 2);
                $table->decimal('discount_percent', 5, 2)->default(0);
                $table->decimal('tax_rate', 5, 2)->default(0);

                // Computed server-side on save; never trusted from the client.
                $table->decimal('taxable_amount', 15, 2)->default(0);
                $table->decimal('discount_amount', 15, 2)->default(0);
                $table->decimal('tax_amount', 15, 2)->default(0);
                $table->decimal('total_amount', 15, 2)->default(0);

                $table->unsignedInteger('position')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('order_status_events')) {
            Schema::create('order_status_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
                $table->string('from_status')->nullable();
                $table->string('to_status');
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('note')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_events');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
