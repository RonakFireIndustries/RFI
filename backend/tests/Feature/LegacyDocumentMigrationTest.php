<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\LedgerAccount;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Voucher;
use App\Services\Accounting\LegacyDocumentMigrator;
use App\Services\Accounting\PartyLedgerService;
use App\Services\Accounting\PartyOutstandingService;
use App\Services\Accounting\VoucherService;
use Database\Seeders\ChartOfAccountsSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Slice 7: the legacy documents move into the books.
 *
 * An invoice becomes a sales voucher and a purchase order becomes a purchase
 * voucher, each idempotently and each worth exactly what the legacy document
 * was worth, so Outstandings shows the same balance the day after the migration
 * as the day before.
 *
 * The legacy tables are built by hand rather than by their own migrations: the
 * real chain depends on branches, sales orders, sites and users, none of which
 * these paths touch. Only the columns the migrator actually reads are created.
 */
class LegacyDocumentMigrationTest extends TestCase
{
    private const ACCOUNTING_MIGRATIONS = [
        '0001_01_01_000000_create_users_table.php',
        '2026_05_30_083453_create_permission_tables.php',
        '2026_08_27_000000_add_is_super_admin_to_users_table.php',
        '2026_10_17_000001_create_account_groups_table.php',
        '2026_10_17_000002_create_ledger_accounts_table.php',
        '2026_10_17_000003_create_financial_years_table.php',
        '2026_10_17_000004_create_vouchers_table.php',
        '2026_10_17_000005_create_voucher_entries_table.php',
        '2026_10_17_000006_create_voucher_bill_allocations_table.php',
        '2026_10_17_000007_create_voucher_items_table.php',
        '2026_10_17_000008_add_document_totals_to_vouchers_table.php',
    ];

    private const LINK_MIGRATION = '2026_10_18_000001_add_accounting_voucher_to_legacy_documents.php';

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (self::ACCOUNTING_MIGRATIONS as $file) {
            (require __DIR__ . '/../../database/migrations/' . $file)->up();
        }

        $this->legacySchema();

        // Runs last: it adds the link column to the tables above.
        (require __DIR__ . '/../../database/migrations/' . self::LINK_MIGRATION)->up();

        (new ChartOfAccountsSeeder)->run();

        $this->user = User::factory()->create();
    }

    /* ----------------------------- legacy tables -------------------------- */

    private function legacySchema(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('gst_number')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
        });

        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('gst_number')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sales_order_id')->nullable();
            $table->string('invoice_number')->unique();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->date('issue_date')->nullable();
            $table->date('due_date')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('cgst_total', 15, 2)->default(0);
            $table->decimal('sgst_total', 15, 2)->default(0);
            $table->decimal('igst_total', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->string('gst_type')->nullable();
            $table->decimal('gst_rate', 5, 2)->default(0);
            $table->string('status')->default('Pending');
            $table->date('payment_date')->nullable();
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->unsignedBigInteger('accounting_voucher_id')->nullable();
            $table->timestamps();
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('item_description');
            $table->string('hsn_code')->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->string('po_number')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('shipping_cost', 15, 2)->default(0);
            $table->decimal('other_cost', 15, 2)->default(0);
            $table->string('status')->default('Pending');
            $table->string('gst_type')->nullable();
            $table->decimal('gst_rate', 5, 2)->default(0);
            $table->unsignedBigInteger('accounting_voucher_id')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_order_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('custom_product_name')->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->decimal('gst_rate', 5, 2)->default(0);
            $table->string('hsn_code')->nullable();
            $table->decimal('total', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->unsignedBigInteger('purchase_order_id')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('payment_method')->nullable();
            $table->date('payment_date')->nullable();
            $table->timestamps();
        });
    }

    /* -------------------------------- fixtures ---------------------------- */

    private function customer(string $name = 'Acme Corp'): int
    {
        return DB::table('customers')->insertGetId([
            'name' => $name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function supplier(string $name = 'Gamma Mills'): int
    {
        return DB::table('suppliers')->insertGetId([
            'name' => $name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * An invoice plus its lines. Header GST lives on the invoice (the legacy
     * per-line tax columns were dropped), so the lines only carry money.
     */
    private function invoice(array $attributes = [], array $items = []): int
    {
        $id = DB::table('invoices')->insertGetId(array_merge([
            'invoice_number' => 'INV-' . str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT),
            'customer_id' => null,
            'issue_date' => '2026-05-01',
            'due_date' => '2026-05-31',
            'total_amount' => 0,
            'subtotal' => 0,
            'cgst_total' => 0,
            'sgst_total' => 0,
            'igst_total' => 0,
            'grand_total' => 0,
            'gst_type' => 'cgst',
            'gst_rate' => 0,
            'status' => 'Pending',
            'paid_amount' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ], $attributes));

        foreach ($items as $item) {
            DB::table('invoice_items')->insert(array_merge([
                'invoice_id' => $id,
                'product_id' => null,
                'item_description' => 'Item',
                'hsn_code' => null,
                'quantity' => 1,
                'unit_price' => 0,
                'discount' => 0,
                'total' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ], $item));
        }

        return $id;
    }

    private function purchaseOrder(array $attributes = [], array $items = []): int
    {
        $id = DB::table('purchase_orders')->insertGetId(array_merge([
            'supplier_id' => null,
            'po_number' => 'PO-' . str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT),
            'total_amount' => 0,
            'tax_amount' => 0,
            'shipping_cost' => 0,
            'other_cost' => 0,
            'status' => 'Approved',
            'gst_type' => 'cgst',
            'gst_rate' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ], $attributes));

        foreach ($items as $item) {
            DB::table('purchase_order_items')->insert(array_merge([
                'purchase_order_id' => $id,
                'product_id' => null,
                'custom_product_name' => null,
                'quantity' => 1,
                'unit_cost' => 0,
                'gst_rate' => 0,
                'hsn_code' => null,
                'total' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ], $item));
        }

        return $id;
    }

    private function linkCustomer(int $id): LedgerAccount
    {
        return app(PartyLedgerService::class)->ensure(Customer::findOrFail($id));
    }

    private function linkSupplier(int $id): LedgerAccount
    {
        return app(PartyLedgerService::class)->ensure(Supplier::findOrFail($id));
    }

    private function outstanding(LedgerAccount $ledger): array
    {
        return app(PartyOutstandingService::class)->forLedger($ledger->fresh());
    }

    /** Ledger name => [type, amount] for a voucher's Dr/Cr lines. */
    private function entryTotals(Voucher $voucher): array
    {
        $out = [];

        foreach ($voucher->entries()->with('ledger')->get() as $entry) {
            $out[$entry->ledger->name] = [
                'type' => $entry->entry_type,
                'amount' => round((float) $entry->amount, 2),
            ];
        }

        return $out;
    }

    private function migrate(bool $dryRun = false): array
    {
        return app(LegacyDocumentMigrator::class)->migrate($dryRun);
    }

    /* -------------------------------- invoices ---------------------------- */

    public function test_an_invoice_becomes_a_sales_voucher_with_gst(): void
    {
        $customerId = $this->customer();
        $invoiceId = $this->invoice([
            'invoice_number' => 'INV-001',
            'customer_id' => $customerId,
            'subtotal' => 200,
            'cgst_total' => 18,
            'sgst_total' => 18,
            'grand_total' => 236,
            'gst_rate' => 18,
        ], [
            ['item_description' => 'Widget', 'quantity' => 2, 'unit_price' => 100],
        ]);

        $summary = $this->migrate();

        $this->assertSame(1, $summary['invoice']['migrated']);
        $this->assertSame([], $summary['invoice']['errors']);

        $voucher = Voucher::where('voucher_type', 'sales')->firstOrFail();

        $this->assertSame('INV-001', $voucher->reference_no);
        $this->assertSame('2026-05-01', $voucher->voucher_date->toDateString());
        $this->assertEquals(236.0, round((float) $voucher->amount, 2));
        $this->assertEquals(200.0, round((float) $voucher->taxable_total, 2));
        $this->assertEquals(36.0, round((float) $voucher->tax_total, 2));
        $this->assertSame('intra', $voucher->gst_type);

        $item = $voucher->items()->firstOrFail();
        $this->assertEquals(2.0, round((float) $item->quantity, 3));
        $this->assertEquals(100.0, round((float) $item->rate, 2));
        $this->assertEquals(18.0, round((float) $item->tax_rate, 2));
        $this->assertEquals(200.0, round((float) $item->taxable_amount, 2));

        // Output tax is a credit, so the whole credit side (Sales + GST) equals
        // the party debit.
        $entries = $this->entryTotals($voucher);
        $this->assertSame(['type' => 'Dr', 'amount' => 236.0], $entries['Acme Corp']);
        $this->assertSame(['type' => 'Cr', 'amount' => 200.0], $entries['Sales']);
        $this->assertSame(['type' => 'Cr', 'amount' => 18.0], $entries['CGST']);
        $this->assertSame(['type' => 'Cr', 'amount' => 18.0], $entries['SGST']);

        $this->assertSame(
            $voucher->id,
            (int) DB::table('invoices')->where('id', $invoiceId)->value('accounting_voucher_id')
        );
    }

    public function test_an_inter_state_invoice_books_igst(): void
    {
        $customerId = $this->customer();
        $this->invoice([
            'customer_id' => $customerId,
            'subtotal' => 1000,
            'igst_total' => 180,
            'grand_total' => 1180,
            'gst_type' => 'igst',
            'gst_rate' => 18,
        ], [
            ['item_description' => 'Machine', 'quantity' => 1, 'unit_price' => 1000],
        ]);

        $this->migrate();

        $voucher = Voucher::where('voucher_type', 'sales')->firstOrFail();
        $this->assertSame('inter', $voucher->gst_type);

        $entries = $this->entryTotals($voucher);
        $this->assertSame(['type' => 'Dr', 'amount' => 1180.0], $entries['Acme Corp']);
        $this->assertSame(['type' => 'Cr', 'amount' => 180.0], $entries['IGST']);
        $this->assertArrayNotHasKey('CGST', $entries);
    }

    public function test_a_line_discount_is_carried_as_a_percentage(): void
    {
        $customerId = $this->customer();
        $this->invoice([
            'customer_id' => $customerId,
            'subtotal' => 180,
            'cgst_total' => 16.2,
            'sgst_total' => 16.2,
            'grand_total' => 212.4,
            'gst_rate' => 18,
        ], [
            ['item_description' => 'Widget', 'quantity' => 2, 'unit_price' => 100, 'discount' => 20],
        ]);

        $this->migrate();

        $item = Voucher::where('voucher_type', 'sales')->firstOrFail()->items()->firstOrFail();

        $this->assertEquals(10.0, round((float) $item->discount_percent, 2));
        $this->assertEquals(20.0, round((float) $item->discount_amount, 2));
        $this->assertEquals(180.0, round((float) $item->taxable_amount, 2));
    }

    public function test_a_header_total_the_lines_do_not_add_up_to_is_held_exact(): void
    {
        // The lines say 100 + 18% = 118, the header says 120. The document is
        // what the customer was billed, so the voucher is worth 120.
        $customerId = $this->customer();
        $this->invoice([
            'customer_id' => $customerId,
            'subtotal' => 100,
            'cgst_total' => 9,
            'sgst_total' => 9,
            'grand_total' => 120,
            'gst_rate' => 18,
        ], [
            ['item_description' => 'Widget', 'quantity' => 1, 'unit_price' => 100],
        ]);

        $summary = $this->migrate();

        $voucher = Voucher::where('voucher_type', 'sales')->firstOrFail();
        $this->assertEquals(120.0, round((float) $voucher->amount, 2));
        $this->assertEquals(2.0, round((float) $voucher->round_off, 2));
        $this->assertSame(1, $summary['residual']['count']);
    }

    public function test_an_invoice_without_lines_still_becomes_a_voucher(): void
    {
        $customerId = $this->customer();
        $this->invoice([
            'customer_id' => $customerId,
            'subtotal' => 500,
            'cgst_total' => 45,
            'sgst_total' => 45,
            'grand_total' => 590,
            'gst_rate' => 18,
        ]);

        $this->migrate();

        $voucher = Voucher::where('voucher_type', 'sales')->firstOrFail();
        $this->assertEquals(590.0, round((float) $voucher->amount, 2));
        $this->assertSame(1, $voucher->items()->count());
        $this->assertEquals(500.0, round((float) $voucher->items()->first()->rate, 2));
    }

    public function test_a_cancelled_invoice_is_left_alone(): void
    {
        $customerId = $this->customer();
        $invoiceId = $this->invoice([
            'customer_id' => $customerId,
            'status' => 'Cancelled',
            'subtotal' => 100,
            'grand_total' => 100,
        ]);

        $ledger = $this->linkCustomer($customerId);
        $this->migrate();

        $this->assertSame(0, Voucher::where('voucher_type', 'sales')->count());
        $this->assertNull(DB::table('invoices')->where('id', $invoiceId)->value('accounting_voucher_id'));

        // Cancelled documents were never in Outstandings, and still are not.
        $this->assertEquals(0.0, $this->outstanding($ledger)['total_outstanding']);
    }

    public function test_an_invoice_with_no_customer_is_reported_not_fatal(): void
    {
        $this->invoice(['customer_id' => null, 'subtotal' => 100, 'grand_total' => 100]);
        $customerId = $this->customer();
        $this->invoice(['customer_id' => $customerId, 'subtotal' => 50, 'grand_total' => 50]);

        $summary = $this->migrate();

        $this->assertSame(1, $summary['invoice']['migrated']);
        $this->assertCount(1, $summary['invoice']['errors']);
        $this->assertStringContainsString('no customer', $summary['invoice']['errors'][0]);
    }

    /* ------------------------------- purchases ---------------------------- */

    public function test_a_purchase_order_becomes_a_purchase_voucher_including_input_gst(): void
    {
        $supplierId = $this->supplier();
        $this->purchaseOrder([
            'po_number' => 'PO-9',
            'supplier_id' => $supplierId,
            'total_amount' => 200,
            'tax_amount' => 36,
            'gst_type' => 'cgst',
        ], [
            ['custom_product_name' => 'Steel', 'quantity' => 2, 'unit_cost' => 100, 'gst_rate' => 18],
        ]);

        $summary = $this->migrate();

        $this->assertSame(1, $summary['purchase-order']['migrated']);
        $this->assertSame([], $summary['purchase-order']['errors']);

        $voucher = Voucher::where('voucher_type', 'purchase')->firstOrFail();
        $this->assertSame('PO-9', $voucher->reference_no);
        $this->assertEquals(236.0, round((float) $voucher->amount, 2));
        $this->assertEquals(200.0, round((float) $voucher->taxable_total, 2));
        $this->assertEquals(36.0, round((float) $voucher->tax_total, 2));

        $entries = $this->entryTotals($voucher);
        $this->assertSame(['type' => 'Cr', 'amount' => 236.0], $entries['Gamma Mills']);
        $this->assertSame(['type' => 'Dr', 'amount' => 200.0], $entries['Purchase']);
        $this->assertSame(['type' => 'Dr', 'amount' => 18.0], $entries['CGST']);
        $this->assertSame(['type' => 'Dr', 'amount' => 18.0], $entries['SGST']);
    }

    public function test_a_purchase_order_migrates_a_per_line_gst_rate(): void
    {
        $supplierId = $this->supplier();
        $this->purchaseOrder([
            'supplier_id' => $supplierId,
            'total_amount' => 300,
            'tax_amount' => 18,
        ], [
            ['custom_product_name' => 'Taxed', 'quantity' => 1, 'unit_cost' => 100, 'gst_rate' => 18],
            ['custom_product_name' => 'Exempt', 'quantity' => 2, 'unit_cost' => 100, 'gst_rate' => 0],
        ]);

        $this->migrate();

        $voucher = Voucher::where('voucher_type', 'purchase')->firstOrFail();
        $this->assertEquals(318.0, round((float) $voucher->amount, 2));

        $rates = $voucher->items()->orderBy('line_no')->pluck('tax_rate')->map(fn ($r) => round((float) $r, 2))->all();
        $this->assertSame([18.0, 0.0], $rates);
    }

    /**
     * The deliberate difference: the legacy system treated a purchase order as
     * its pre-tax `total_amount`, so a PO with GST re-opens for the tax too —
     * which is what the supplier actually billed, and what books the input
     * credit. A zero-rated PO is unchanged.
     */
    public function test_purchase_order_outstanding_gains_the_legacy_tax(): void
    {
        $supplierId = $this->supplier();
        $this->purchaseOrder([
            'supplier_id' => $supplierId,
            'total_amount' => 200,
            'tax_amount' => 36,
        ], [
            ['quantity' => 2, 'unit_cost' => 100, 'gst_rate' => 18],
        ]);
        $this->purchaseOrder([
            'supplier_id' => $supplierId,
            'total_amount' => 300,
            'tax_amount' => 0,
        ], [
            ['quantity' => 3, 'unit_cost' => 100, 'gst_rate' => 0],
        ]);

        $ledger = $this->linkSupplier($supplierId);
        $before = $this->outstanding($ledger);
        $this->assertEquals(500.0, $before['total_outstanding']);

        $this->migrate();

        // 236 (with input GST) + 300 (zero rated, unchanged).
        $this->assertEquals(536.0, $this->outstanding($ledger)['total_outstanding']);
    }

    public function test_purchase_order_without_lines_still_becomes_a_voucher(): void
    {
        $supplierId = $this->supplier();
        $this->purchaseOrder([
            'supplier_id' => $supplierId,
            'total_amount' => 400,
            'tax_amount' => 72,
        ]);

        $this->migrate();

        $voucher = Voucher::where('voucher_type', 'purchase')->firstOrFail();
        $this->assertEquals(472.0, round((float) $voucher->amount, 2));
        $this->assertEquals(400.0, round((float) $voucher->items()->first()->rate, 2));
    }

    public function test_freight_is_reported_but_not_booked(): void
    {
        $supplierId = $this->supplier();
        $this->purchaseOrder([
            'supplier_id' => $supplierId,
            'total_amount' => 100,
            'tax_amount' => 0,
            'shipping_cost' => 50,
            'other_cost' => 25,
        ], [
            ['quantity' => 1, 'unit_cost' => 100, 'gst_rate' => 0],
        ]);

        $summary = $this->migrate();

        $this->assertEquals(100.0, round((float) Voucher::where('voucher_type', 'purchase')->firstOrFail()->amount, 2));
        $this->assertSame(1, $summary['shipping']['count']);
        $this->assertEquals(75.0, round($summary['shipping']['amount'], 2));
    }

    /* ---------------------------- outstandings ---------------------------- */

    public function test_a_part_paid_invoice_keeps_the_same_outstanding(): void
    {
        $customerId = $this->customer();
        $this->invoice([
            'customer_id' => $customerId,
            'subtotal' => 1000,
            'cgst_total' => 90,
            'sgst_total' => 90,
            'grand_total' => 1180,
            'gst_rate' => 18,
            'paid_amount' => 400,
        ], [
            ['quantity' => 1, 'unit_price' => 1000],
        ]);

        $ledger = $this->linkCustomer($customerId);

        $before = $this->outstanding($ledger);
        $this->assertEquals(780.0, $before['total_outstanding']);
        $this->assertSame('invoice', $before['bills'][0]['bill_type']);

        $this->migrate();

        $after = $this->outstanding($ledger);
        $this->assertSame(780.0, $after['total_outstanding']);
        $this->assertSame('sales-voucher', $after['bills'][0]['bill_type']);
        $this->assertEquals(1180.0, $after['bills'][0]['total']);
        $this->assertEquals(400.0, $after['bills'][0]['settled']);
    }

    public function test_a_part_paid_purchase_order_keeps_the_same_outstanding_when_zero_rated(): void
    {
        $supplierId = $this->supplier();
        $poId = $this->purchaseOrder([
            'supplier_id' => $supplierId,
            'total_amount' => 500,
            'tax_amount' => 0,
        ], [
            ['quantity' => 5, 'unit_cost' => 100, 'gst_rate' => 0],
        ]);

        DB::table('payments')->insert([
            'purchase_order_id' => $poId,
            'amount' => 200,
            'payment_method' => 'Bank',
            'payment_date' => '2026-06-01',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $ledger = $this->linkSupplier($supplierId);
        $this->assertEquals(300.0, $this->outstanding($ledger)['total_outstanding']);

        $this->migrate();

        $after = $this->outstanding($ledger);
        $this->assertSame(300.0, $after['total_outstanding']);
        $this->assertSame('purchase-voucher', $after['bills'][0]['bill_type']);
        $this->assertEquals(200.0, $after['bills'][0]['settled']);
    }

    public function test_the_settled_part_is_posted_as_one_journal_per_party(): void
    {
        $customerId = $this->customer();
        $this->invoice([
            'customer_id' => $customerId,
            'subtotal' => 1000,
            'cgst_total' => 90,
            'sgst_total' => 90,
            'grand_total' => 1180,
            'gst_rate' => 18,
            'paid_amount' => 400,
        ], [
            ['quantity' => 1, 'unit_price' => 1000],
        ]);
        $this->invoice([
            'customer_id' => $customerId,
            'subtotal' => 500,
            'grand_total' => 500,
            'paid_amount' => 100,
        ], [
            ['quantity' => 1, 'unit_price' => 500],
        ]);

        $summary = $this->migrate();

        $this->assertSame(2, $summary['invoice']['migrated']);
        $this->assertSame(1, $summary['settlement']['journals']);
        $this->assertEquals(500.0, round($summary['settlement']['amount'], 2));

        $journal = Voucher::where('reference_no', LegacyDocumentMigrator::SETTLEMENT_REFERENCE)->firstOrFail();

        $this->assertSame('journal', $journal->voucher_type);
        $this->assertEquals(500.0, round((float) $journal->amount, 2));

        $entries = $this->entryTotals($journal);
        $this->assertSame(['type' => 'Dr', 'amount' => 500.0], $entries['Migration Suspense']);
        $this->assertSame(['type' => 'Cr', 'amount' => 500.0], $entries['Acme Corp']);

        $this->assertSame(2, $journal->billAllocations()->count());
        $this->assertSame('sales-voucher', $journal->billAllocations()->first()->bill_type);
        $this->assertSame('Migration Suspense', LedgerAccount::findByName('Migration Suspense')->name);

        // Two documents, one journal: the party is settled once, not twice.
        $this->assertEquals(1, Voucher::where('voucher_type', 'journal')->count());
    }

    public function test_a_settled_supplier_is_debited_against_suspense(): void
    {
        $supplierId = $this->supplier();
        $poId = $this->purchaseOrder([
            'supplier_id' => $supplierId,
            'total_amount' => 500,
            'tax_amount' => 0,
        ], [
            ['quantity' => 5, 'unit_cost' => 100, 'gst_rate' => 0],
        ]);

        DB::table('payments')->insert([
            'purchase_order_id' => $poId,
            'amount' => 200,
            'payment_date' => '2026-06-01',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->migrate();

        $entries = $this->entryTotals(
            Voucher::where('reference_no', LegacyDocumentMigrator::SETTLEMENT_REFERENCE)->firstOrFail()
        );

        $this->assertSame(['type' => 'Dr', 'amount' => 200.0], $entries['Gamma Mills']);
        $this->assertSame(['type' => 'Cr', 'amount' => 200.0], $entries['Migration Suspense']);
    }

    /**
     * A receipt entered before the migration settled the legacy bill by id.
     * Once the document is a voucher that settlement has to follow it, or it
     * would be orphaned and the bill would re-open.
     */
    public function test_an_existing_allocation_follows_the_document(): void
    {
        $customerId = $this->customer();
        $invoiceId = $this->invoice([
            'customer_id' => $customerId,
            'subtotal' => 1000,
            'cgst_total' => 90,
            'sgst_total' => 90,
            'grand_total' => 1180,
            'gst_rate' => 18,
        ], [
            ['quantity' => 1, 'unit_price' => 1000],
        ]);

        $ledger = $this->linkCustomer($customerId);
        $cash = LedgerAccount::findByName('Cash');

        app(VoucherService::class)->create([
            'voucher_type' => 'receipt',
            'voucher_date' => '2026-05-02',
            'party_ledger_id' => $ledger->id,
            'entries' => [
                ['ledger_id' => $cash->id, 'entry_type' => 'Dr', 'amount' => 100],
                ['ledger_id' => $ledger->id, 'entry_type' => 'Cr', 'amount' => 100],
            ],
            'bill_allocations' => [
                ['bill_type' => 'invoice', 'bill_id' => $invoiceId, 'amount' => 100],
            ],
        ], $this->user->id);

        $this->assertEquals(1080.0, $this->outstanding($ledger)['total_outstanding']);

        $this->migrate();

        $voucher = Voucher::where('voucher_type', 'sales')->firstOrFail();
        $allocation = DB::table('voucher_bill_allocations')->where('bill_type', 'sales-voucher')->first();

        $this->assertNotNull($allocation);
        $this->assertSame($voucher->id, (int) $allocation->bill_id);
        $this->assertEquals(1080.0, $this->outstanding($ledger)['total_outstanding']);
    }

    /* ------------------------- idempotency / safety ----------------------- */

    public function test_the_migration_is_idempotent(): void
    {
        $customerId = $this->customer();
        $this->invoice([
            'customer_id' => $customerId,
            'subtotal' => 1000,
            'grand_total' => 1000,
            'paid_amount' => 250,
        ], [
            ['quantity' => 1, 'unit_price' => 1000],
        ]);

        $first = $this->migrate();
        $second = $this->migrate();

        $this->assertSame(1, $first['invoice']['migrated']);
        $this->assertSame(0, $second['invoice']['migrated']);
        $this->assertSame(1, $second['invoice']['skipped']);

        $this->assertSame(1, Voucher::where('voucher_type', 'sales')->count());
        $this->assertSame(1, Voucher::where('voucher_type', 'journal')->count());
        $this->assertEquals(750.0, round((float) $this->outstanding($this->linkCustomer($customerId))['total_outstanding'], 2));
    }

    public function test_a_dry_run_writes_nothing(): void
    {
        $customerId = $this->customer();
        $this->invoice([
            'customer_id' => $customerId,
            'subtotal' => 1000,
            'grand_total' => 1000,
            'paid_amount' => 250,
        ], [
            ['quantity' => 1, 'unit_price' => 1000],
        ]);

        $summary = $this->migrate(true);

        $this->assertSame(1, $summary['invoice']['migrated']);
        $this->assertEquals(250.0, round($summary['settlement']['amount'], 2));

        $this->assertSame(0, Voucher::count());
        $this->assertNull(LedgerAccount::findByName('Migration Suspense'));
        $this->assertSame(0, LedgerAccount::where('party_type', 'customer')->count());
        $this->assertNull(DB::table('invoices')->value('accounting_voucher_id'));
    }

    public function test_rollback_puts_the_books_back(): void
    {
        $customerId = $this->customer();
        $this->invoice([
            'customer_id' => $customerId,
            'invoice_number' => 'INV-ROLL',
            'subtotal' => 1000,
            'grand_total' => 1000,
            'paid_amount' => 250,
        ], [
            ['quantity' => 1, 'unit_price' => 1000],
        ]);

        $ledger = $this->linkCustomer($customerId);
        $this->migrate();

        $this->assertSame(2, Voucher::count());

        $summary = app(LegacyDocumentMigrator::class)->rollback();

        $this->assertSame(1, $summary['invoice']);
        $this->assertSame(1, $summary['settlement']);
        $this->assertSame([], $summary['errors']);

        $this->assertSame(0, Voucher::count());
        $this->assertNull(DB::table('invoices')->where('invoice_number', 'INV-ROLL')->value('accounting_voucher_id'));

        $after = $this->outstanding($ledger);
        $this->assertEquals(750.0, $after['total_outstanding']);
        $this->assertSame('invoice', $after['bills'][0]['bill_type']);
    }

    public function test_a_missing_chart_of_accounts_is_a_clear_failure(): void
    {
        $customerId = $this->customer();
        $this->invoice(['customer_id' => $customerId, 'subtotal' => 100, 'grand_total' => 100]);

        // Wipe the seeded chart, leaving the party masters behind.
        DB::table('ledger_accounts')->delete();
        DB::table('account_groups')->delete();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('ChartOfAccountsSeeder');

        $this->migrate();
    }

    /* -------------------------------- command ----------------------------- */

    public function test_the_command_runs_a_dry_run_then_commits(): void
    {
        $customerId = $this->customer();
        $this->invoice([
            'customer_id' => $customerId,
            'subtotal' => 100,
            'grand_total' => 100,
        ], [
            ['quantity' => 1, 'unit_price' => 100],
        ]);

        $this->artisan('accounting:migrate-legacy', ['--dry-run' => true])->assertExitCode(0);
        $this->assertSame(0, Voucher::count());

        $this->artisan('accounting:migrate-legacy')->assertExitCode(0);
        $this->assertSame(1, Voucher::where('voucher_type', 'sales')->count());

        $this->artisan('accounting:migrate-legacy', ['--rollback' => true])->assertExitCode(0);
        $this->assertSame(0, Voucher::count());
    }
}
