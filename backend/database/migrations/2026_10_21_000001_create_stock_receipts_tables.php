<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Goods receipt tracking on item vouchers.
 *
 * A purchase bill is booked the day it arrives, but the goods behind it can
 * land in instalments — so `voucher_items` needs to remember how much of each
 * line has actually been received, and those receipts need a home.
 *
 * `stock_receipts` is one physical delivery; `stock_receipt_items` are its
 * lines, each pointing back at the voucher line it fulfils. Deliveries against
 * a sales voucher reuse the same two tables through `voucher_type == 'sales'`,
 * so the "how much has gone out, when, and to whom" question is answered for
 * both directions by one query.
 *
 * The `received_quantity` / `delivered_quantity` counters on `voucher_items`
 * are denormalised totals of the active (non-reversed) receipt lines. They
 * exist so list screens and "still pending" maths stay a single query; the
 * StockReceiptService is the only writer.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('voucher_items', 'received_quantity')) {
            Schema::table('voucher_items', function (Blueprint $table) {
                // purchase / debit note vouchers: goods in
                $table->decimal('received_quantity', 15, 3)->default(0)->after('quantity');
                // sales vouchers: goods out
                $table->decimal('delivered_quantity', 15, 3)->default(0)->after('received_quantity');
            });
        }

        if (! Schema::hasTable('stock_receipts')) {
            Schema::create('stock_receipts', function (Blueprint $table) {
                $table->id();
                $table->string('receipt_no')->unique();       // GRN-2026-0001 / DLV-2026-0001

                $table->string('voucher_type');               // purchase | debit-note | sales
                $table->foreignId('voucher_id')->constrained('vouchers')->cascadeOnDelete();

                $table->date('receipt_date');

                // The voucher's party, copied so a receipt still reads correctly
                // even if the ledger is later relinked.
                $table->foreignId('party_ledger_id')->nullable()
                    ->constrained('ledger_accounts')->nullOnDelete();

                // Where the goods landed (in) or came from (out). Polymorphic,
                // matching product_stock / transaction_ledger: App\Models\Site.
                $table->string('location_type')->nullable();
                $table->unsignedBigInteger('location_id')->nullable();

                $table->string('status')->default('active');  // active | reversed
                $table->string('reversal_reason')->nullable();
                $table->text('notes')->nullable();

                // Set when a purchase receipt was raised from the supplier's
                // bill (the bill number, deliberately not an FK: the bill lives
                // outside the system).
                $table->string('supplier_bill_no')->nullable();
                $table->date('supplier_bill_date')->nullable();

                $table->string('attachment_path')->nullable();

                $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('reversed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('reversed_at')->nullable();
                $table->timestamps();

                $table->index(['voucher_id', 'status']);
                $table->index(['voucher_type', 'receipt_date']);
                $table->index(['location_type', 'location_id']);
                $table->index('receipt_date');
            });
        }

        if (! Schema::hasTable('stock_receipt_items')) {
            Schema::create('stock_receipt_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('stock_receipt_id')->constrained()->cascadeOnDelete();
                $table->foreignId('voucher_item_id')->constrained('voucher_items')->cascadeOnDelete();
                $table->unsignedBigInteger('product_id')->nullable()->index();

                $table->decimal('quantity', 15, 3);

                // Running total of this voucher line after this receipt, so the
                // history reads as a ledger of its own.
                $table->decimal('quantity_before', 15, 3)->default(0);
                $table->decimal('quantity_after', 15, 3)->default(0);

                // The stock movement this line produced (nullable: the entry is
                // deleted when the receipt is reversed).
                $table->unsignedBigInteger('transaction_ledger_id')->nullable()->index();
                // Free-text batch / serial reference from the delivery note.
                $table->string('batch_no')->nullable();

                $table->timestamps();

                $table->index(['voucher_item_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_receipt_items');
        Schema::dropIfExists('stock_receipts');

        if (Schema::hasColumn('voucher_items', 'received_quantity')) {
            Schema::table('voucher_items', function (Blueprint $table) {
                $table->dropColumn(['received_quantity', 'delivered_quantity']);
            });
        }
    }
};
