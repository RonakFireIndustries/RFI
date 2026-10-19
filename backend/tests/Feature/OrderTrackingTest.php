<?php

namespace Tests\Feature;

use App\Models\AccountGroup;
use App\Models\Customer;
use App\Models\FinancialYear;
use App\Models\LedgerAccount;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Support\Carbon;
use Illuminate\Testing\TestResponse;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Order tracking (operational): numbering, line totals, the five-stage
 * pipeline, the invoice link its payment panel reads, and the `orders`
 * access module. Orders never write to the ledger — that stays with the
 * purchase / sales vouchers (see PartyCreditDaysTest for the due dates).
 *
 * As with the other accounting suites, only the migrations these endpoints
 * need are run — the full chain is MySQL-specific in places.
 */
class OrderTrackingTest extends TestCase
{
    private const MIGRATIONS = [
        '0001_01_01_000000_create_users_table.php',
        '2026_05_30_083453_create_permission_tables.php',
        '2026_08_27_000000_add_is_super_admin_to_users_table.php',
        '2026_06_06_085506_create_suppliers_table.php',
        '2026_06_06_085507_create_customers_table.php',
        '2026_10_17_000001_create_account_groups_table.php',
        '2026_10_17_000002_create_ledger_accounts_table.php',
        '2026_10_17_000003_create_financial_years_table.php',
        '2026_10_17_000004_create_vouchers_table.php',
        '2026_10_17_000005_create_voucher_entries_table.php',
        '2026_10_17_000006_create_voucher_bill_allocations_table.php',
        '2026_10_17_000007_create_voucher_items_table.php',
        '2026_10_17_000008_add_document_totals_to_vouchers_table.php',
        '2026_10_19_000001_add_due_date_to_vouchers.php',
        '2026_10_19_000002_add_credit_days_to_parties.php',
        '2026_10_19_000003_create_orders_tables.php',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        foreach (self::MIGRATIONS as $file) {
            $migration = require __DIR__ . '/../../database/migrations/' . $file;
            $migration->up();
        }

        FinancialYear::create([
            'name' => '2026-2027',
            'start_date' => '2026-04-01',
            'end_date' => '2027-03-31',
            'is_active' => true,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /* ----------------------------- fixtures ----------------------------- */

    private function superAdmin(): User
    {
        return User::factory()->create(['is_super_admin' => true]);
    }

    private function userWithPermissions(array $names): User
    {
        foreach ($names as $name) {
            Permission::findOrCreate($name, 'web');
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $user = User::factory()->create();
        $user->givePermissionTo($names);

        return $user;
    }

    private function ledger(string $name, string $nature, array $extra = []): LedgerAccount
    {
        $group = AccountGroup::create([
            'name' => $name . ' Group',
            'nature' => $nature,
            'is_primary' => true,
            'is_party_group' => $extra['is_party_group'] ?? false,
            'party_type' => $extra['party_type'] ?? null,
        ]);

        return LedgerAccount::create(array_merge([
            'name' => $name,
            'group_id' => $group->id,
        ], array_diff_key($extra, ['is_party_group' => null])));
    }

    /**
     * A customer master with its terms, plus the ledger
     * `accounting:link-parties` would hang off it.
     *
     * @return array{0: Customer, 1: LedgerAccount}
     */
    private function customer(string $name = 'Acme Corp', ?int $creditDays = null): array
    {
        $customer = Customer::create(['name' => $name, 'credit_days' => $creditDays]);

        // PartyLedgerObserver links a ledger to every master row the moment it
        // exists (fail-soft until a party group is set up). Take whichever one
        // won rather than inserting a second row into a unique name.
        $ledger = LedgerAccount::where('party_type', 'customer')
            ->where('party_id', $customer->id)
            ->first()
            ?? $this->ledger($name, 'Assets', [
                'is_party_group' => true,
                'party_type' => 'customer',
                'party_id' => $customer->id,
            ]);

        return [$customer, $ledger];
    }

    /** The default ledgers an item voucher's Dr/Cr lines derive from. */
    private function seedDefaults(): void
    {
        $this->ledger('Sales', 'Income');
        $this->ledger('Purchase', 'Expenses');
        $this->ledger('CGST', 'Liabilities');
        $this->ledger('SGST', 'Liabilities');
        $this->ledger('IGST', 'Liabilities');
        $this->ledger('Round Off', 'Expenses');
    }

    /**
     * One line: 2 × 500 = 1000, less 10% = 900 taxable, +18% = 162 tax,
     * 1062 overall.
     */
    private function line(array $extra = []): array
    {
        return array_merge([
            'item_description' => 'Item A',
            'quantity' => 2,
            'rate' => 500,
            'discount_percent' => 10,
            'tax_rate' => 18,
        ], $extra);
    }

    /** @return array<string, mixed> */
    private function salesPayload(Customer $customer, array $extra = []): array
    {
        return array_merge([
            'side' => 'sales',
            'customer_id' => $customer->id,
            'order_date' => '2026-05-01',
            'expected_delivery_date' => '2026-05-10',
            'items' => [$this->line()],
        ], $extra);
    }

    /** A posted sales voucher the order can be marked invoiced against. */
    private function salesVoucher(LedgerAccount $ledger): Voucher
    {
        $response = $this->postJson('/api/v1/vouchers', [
            'voucher_type' => 'sales',
            'voucher_date' => '2026-05-01',
            'party_ledger_id' => $ledger->id,
            'gst_type' => 'intra',
            'items' => [
                ['item_description' => 'Item A', 'quantity' => 2, 'rate' => 500, 'tax_rate' => 18],
            ],
        ]);

        $response->assertStatus(201);

        return Voucher::findOrFail($response->json('data.id'));
    }

    /** Walk an order to `fulfilled`, one stage short of needing a document. */
    private function toFulfilled(int $orderId): void
    {
        $this->postJson("/api/v1/orders/{$orderId}/advance")->assertOk();
        $this->postJson("/api/v1/orders/{$orderId}/advance")->assertOk();
    }

    /** The validation errors of a failed request, however the wrapper shaped them. */
    private function errors(TestResponse $response): array
    {
        return $response->json('errors') ?? $response->json('data.errors') ?? [];
    }

    /* ------------------------------ creation ---------------------------- */

    public function test_it_raises_a_sales_order_with_server_side_totals_and_a_timeline(): void
    {
        $this->actingAs($this->superAdmin());
        [$customer] = $this->customer();

        $response = $this->postJson('/api/v1/orders', $this->salesPayload($customer));

        $response->assertStatus(201);
        $order = $response->json('data');

        $this->assertSame('SO-2026-0001', $order['order_no']);
        $this->assertSame('sales', $order['side']);
        $this->assertSame('open', $order['status']);
        $this->assertSame('Acme Corp', $order['customer']['name']);
        // Recomputed server-side: the client's figures are never trusted.
        $this->assertSame(900.0, (float) $order['subtotal']);
        $this->assertSame(100.0, (float) $order['discount_total']);
        $this->assertSame(162.0, (float) $order['tax_total']);
        $this->assertSame(1062.0, (float) $order['total_amount']);
        $this->assertCount(1, $order['items']);
        $this->assertSame('open', $order['events'][0]['to_status']);
        $this->assertNull($order['events'][0]['from_status']);
    }

    public function test_numbers_sequence_per_side(): void
    {
        $this->actingAs($this->superAdmin());
        [$customer] = $this->customer();
        $supplier = Supplier::create(['name' => 'Vendor Ltd']);

        $this->postJson('/api/v1/orders', $this->salesPayload($customer))->assertStatus(201);
        $second = $this->postJson('/api/v1/orders', $this->salesPayload($customer));
        $purchase = $this->postJson('/api/v1/orders', [
            'side' => 'purchase',
            'supplier_id' => $supplier->id,
            'order_date' => '2026-05-02',
            'items' => [$this->line()],
        ]);

        $this->assertSame('SO-2026-0002', $second->json('data.order_no'));
        $this->assertSame('PO-2026-0001', $purchase->json('data.order_no'));
    }

    public function test_it_rejects_an_order_without_its_party(): void
    {
        $this->actingAs($this->superAdmin());
        [$customer] = $this->customer();

        $response = $this->postJson('/api/v1/orders', $this->salesPayload($customer, [
            'customer_id' => null,
        ]));

        $response->assertStatus(422);
        $this->assertArrayHasKey('customer_id', $this->errors($response));
    }

    /* ------------------------------- pipeline --------------------------- */

    public function test_it_walks_the_pipeline_and_demands_a_document_at_invoiced(): void
    {
        $this->actingAs($this->superAdmin());
        $this->seedDefaults();
        [$customer, $ledger] = $this->customer('Acme Corp', 30);

        $order = $this->postJson('/api/v1/orders', $this->salesPayload($customer))->json('data');

        $this->postJson("/api/v1/orders/{$order['id']}/advance")->assertOk();
        $this->postJson("/api/v1/orders/{$order['id']}/advance")->assertOk();

        // Fulfilled: the next stage is `invoiced`, which needs the bill.
        $missing = $this->postJson("/api/v1/orders/{$order['id']}/advance");
        $missing->assertStatus(422);
        $this->assertArrayHasKey('document_id', $this->errors($missing));

        $voucher = $this->salesVoucher($ledger);

        $linked = $this->postJson("/api/v1/orders/{$order['id']}/advance", [
            'document_type' => 'sales-voucher',
            'document_id' => $voucher->id,
            'note' => 'Invoiced against ' . $voucher->voucher_no,
        ]);

        $linked->assertOk();
        $this->assertSame('invoiced', $linked->json('data.status'));
        $this->assertSame($voucher->id, (int) $linked->json('data.document_id'));
        $this->assertSame('sales-voucher', $linked->json('data.document_type'));

        $done = $this->postJson("/api/v1/orders/{$order['id']}/advance");
        $done->assertOk();
        $this->assertSame('done', $done->json('data.status'));

        // Terminal: nothing advances out of `done`.
        $this->postJson("/api/v1/orders/{$order['id']}/advance")->assertStatus(422);

        $events = $done->json('data.events');
        $this->assertCount(5, $events); // created + four advances
        $this->assertSame('invoiced', $events[3]['to_status']);
        $this->assertSame('Invoiced against ' . $voucher->voucher_no, $events[3]['note']);
    }

    public function test_a_document_from_another_party_is_rejected(): void
    {
        $this->actingAs($this->superAdmin());
        $this->seedDefaults();

        // The order is for Beta, but the bill below belongs to Acme.
        [, $acmeLedger] = $this->customer('Acme Corp');
        [$beta] = $this->customer('Beta Ltd');

        $order = $this->postJson('/api/v1/orders', $this->salesPayload($beta))->json('data');
        $this->toFulfilled($order['id']);

        $voucher = $this->salesVoucher($acmeLedger);

        $response = $this->postJson("/api/v1/orders/{$order['id']}/advance", [
            'document_type' => 'sales-voucher',
            'document_id' => $voucher->id,
        ]);

        $response->assertStatus(422);
        $this->assertArrayHasKey('document_id', $this->errors($response));
    }

    /* -------------------------- edit / cancel rules --------------------- */

    public function test_it_edits_only_while_in_flight(): void
    {
        $this->actingAs($this->superAdmin());
        [$customer] = $this->customer();

        $order = $this->postJson('/api/v1/orders', $this->salesPayload($customer))->json('data');

        $edited = $this->putJson("/api/v1/orders/{$order['id']}", [
            'order_date' => '2026-05-01',
            'expected_delivery_date' => '2026-05-20',
            'items' => [$this->line(['rate' => 600])],
        ]);

        $edited->assertOk();
        // 2 × 600 = 1200, less 10% = 1080 taxable, +18% = 194.40.
        $this->assertSame(1080.0, (float) $edited->json('data.subtotal'));
        $this->assertSame(194.4, (float) $edited->json('data.tax_total'));

        $this->toFulfilled($order['id']);

        $rejected = $this->putJson("/api/v1/orders/{$order['id']}", [
            'order_date' => '2026-05-01',
            'notes' => 'too late',
        ]);

        $rejected->assertStatus(422);
        $this->assertArrayHasKey('status', $this->errors($rejected));
    }

    public function test_it_cancels_only_while_the_order_is_live(): void
    {
        $this->actingAs($this->superAdmin());
        [$customer] = $this->customer();

        $order = $this->postJson('/api/v1/orders', $this->salesPayload($customer))->json('data');

        $cancelled = $this->postJson("/api/v1/orders/{$order['id']}/cancel", [
            'note' => 'Customer changed their mind',
        ]);

        $cancelled->assertOk();
        $this->assertSame('cancelled', $cancelled->json('data.status'));
        // The cancel is the second event, recorded against the stage it left.
        $this->assertSame('cancelled', $cancelled->json('data.events.1.to_status'));
        $this->assertSame('open', $cancelled->json('data.events.1.from_status'));

        // Cancelled is terminal: no advancing, no re-cancelling.
        $this->postJson("/api/v1/orders/{$order['id']}/advance")->assertStatus(422);
        $this->postJson("/api/v1/orders/{$order['id']}/cancel")->assertStatus(422);
    }

    /* ------------------------------ payments ---------------------------- */

    public function test_show_reports_the_linked_bill_ageing_and_overdue_flag(): void
    {
        Carbon::setTestNow('2026-06-15 12:00:00');

        $this->actingAs($this->superAdmin());
        $this->seedDefaults();
        [$customer, $ledger] = $this->customer('Acme Corp', 30); // due 2026-05-31

        $order = $this->postJson('/api/v1/orders', $this->salesPayload($customer))->json('data');
        $this->toFulfilled($order['id']);

        $voucher = $this->salesVoucher($ledger);
        $this->postJson("/api/v1/orders/{$order['id']}/advance", [
            'document_type' => 'sales-voucher',
            'document_id' => $voucher->id,
        ])->assertOk();

        $show = $this->getJson("/api/v1/orders/{$order['id']}")->assertOk()->json('data');

        $this->assertSame('sales-voucher', $show['document']['type']);
        $this->assertSame($voucher->voucher_no, $show['document']['number']);

        $bill = $show['payment']['bill'];
        $this->assertSame('2026-05-31', $bill['due_date']);
        $this->assertSame(1180.0, (float) $bill['balance']);
        $this->assertSame(1180.0, (float) $show['payment']['party_outstanding']);
        $this->assertTrue($bill['overdue']);
        $this->assertSame(15, $bill['days']); // 15 days past the due date
        $this->assertSame('1-30 days', $bill['bucket']);

        // Settle it: the bill stops being outstanding, and the party total
        // follows it to zero.
        $bank = $this->ledger('HDFC Bank', 'Assets', ['is_bank' => true]);

        $this->postJson('/api/v1/vouchers', [
            'voucher_type' => 'receipt',
            'voucher_date' => '2026-06-10',
            'party_ledger_id' => $ledger->id,
            'entries' => [
                ['ledger_id' => $bank->id, 'entry_type' => 'Dr', 'amount' => 1180],
                ['ledger_id' => $ledger->id, 'entry_type' => 'Cr', 'amount' => 1180],
            ],
            'bill_allocations' => [
                ['bill_type' => 'sales-voucher', 'bill_id' => $voucher->id, 'amount' => 1180],
            ],
        ])->assertStatus(201);

        $settled = $this->getJson("/api/v1/orders/{$order['id']}")->assertOk()->json('data');

        $this->assertNull($settled['payment']['bill']);
        $this->assertSame(0.0, (float) $settled['payment']['party_outstanding']);
        // The link itself survives settlement — only the balance is gone.
        $this->assertSame($voucher->voucher_no, $settled['document']['number']);
    }

    public function test_an_order_without_a_document_reports_no_payment(): void
    {
        $this->actingAs($this->superAdmin());
        [$customer] = $this->customer();

        $order = $this->postJson('/api/v1/orders', $this->salesPayload($customer))->json('data');
        $show = $this->getJson("/api/v1/orders/{$order['id']}")->assertOk()->json('data');

        $this->assertNull($show['document']);
        $this->assertNull($show['payment']);
    }

    /* ----------------------------- permissions -------------------------- */

    public function test_the_orders_module_gates_every_action(): void
    {
        [$customer] = $this->customer();

        // No grant at all: not even the list.
        $stranger = User::factory()->create();
        $this->actingAs($stranger)->getJson('/api/v1/orders')->assertStatus(403);

        // `orders.view` reads but cannot raise.
        $viewer = $this->userWithPermissions(['orders.view']);
        $this->actingAs($viewer)->getJson('/api/v1/orders')->assertOk();
        $this->actingAs($viewer)
            ->postJson('/api/v1/orders', $this->salesPayload($customer))
            ->assertStatus(403);

        // `orders.create` raises but cannot advance or cancel.
        $creator = $this->userWithPermissions(['orders.view', 'orders.create']);
        $order = $this->actingAs($creator)
            ->postJson('/api/v1/orders', $this->salesPayload($customer))
            ->assertStatus(201)
            ->json('data');

        $this->actingAs($creator)->postJson("/api/v1/orders/{$order['id']}/advance")->assertStatus(403);
        $this->actingAs($creator)->postJson("/api/v1/orders/{$order['id']}/cancel")->assertStatus(403);

        // `orders.fulfil` walks the pipeline but still cannot cancel.
        $operator = $this->userWithPermissions(['orders.view', 'orders.fulfil']);
        $this->actingAs($operator)->postJson("/api/v1/orders/{$order['id']}/advance")->assertOk();
        $this->actingAs($operator)->postJson("/api/v1/orders/{$order['id']}/cancel")->assertStatus(403);

        // `orders.cancel` is its own grant.
        $canceller = $this->userWithPermissions(['orders.cancel']);
        $this->actingAs($canceller)->postJson("/api/v1/orders/{$order['id']}/cancel")->assertOk();
    }
}
