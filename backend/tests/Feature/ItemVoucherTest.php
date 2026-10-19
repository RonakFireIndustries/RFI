<?php

namespace Tests\Feature;

use App\Models\AccountGroup;
use App\Models\FinancialYear;
use App\Models\LedgerAccount;
use App\Models\User;
use App\Models\Voucher;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Covers the item-based vouchers (slice 3): Purchase, Sales, Credit Note and
 * Debit Note — line items, GST derivation, round off, numbering, updates and
 * permission enforcement.
 *
 * As with AccountingTest / VoucherTest, only the migrations these endpoints
 * need are run — the full chain is MySQL-specific in places.
 */
class ItemVoucherTest extends TestCase
{
    private const MIGRATIONS = [
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
        '2026_10_19_000001_add_due_date_to_vouchers.php',
        '2026_10_19_000002_add_credit_days_to_parties.php',
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

    /** The default ledgers an item voucher's Dr/Cr lines derive from. */
    private function seedDefaults(): array
    {
        return [
            'sales' => $this->ledger('Sales', 'Income'),
            'purchase' => $this->ledger('Purchase', 'Expenses'),
            'cgst' => $this->ledger('CGST', 'Liabilities'),
            'sgst' => $this->ledger('SGST', 'Liabilities'),
            'igst' => $this->ledger('IGST', 'Liabilities'),
            'round' => $this->ledger('Round Off', 'Expenses'),
        ];
    }

    private function customer(string $name = 'Acme Corp'): LedgerAccount
    {
        return $this->ledger($name, 'Assets', [
            'is_party_group' => true, 'party_type' => 'customer',
        ]);
    }

    private function supplier(string $name = 'Vendor Ltd'): LedgerAccount
    {
        return $this->ledger($name, 'Liabilities', [
            'is_party_group' => true, 'party_type' => 'supplier',
        ]);
    }

    /** @return array<string, mixed>|null */
    private function entryFor(array $entries, int $ledgerId): ?array
    {
        foreach ($entries as $entry) {
            if ((int) $entry['ledger_id'] === $ledgerId) {
                return $entry;
            }
        }

        return null;
    }

    /* ------------------------------- sales ------------------------------- */

    public function test_it_creates_a_sales_voucher_with_items_and_gst(): void
    {
        $this->actingAs($this->superAdmin());
        $customer = $this->customer();
        $l = $this->seedDefaults();

        $response = $this->postJson('/api/v1/vouchers', [
            'voucher_type' => 'sales',
            'voucher_date' => '2026-05-01',
            'party_ledger_id' => $customer->id,
            'gst_type' => 'intra',
            'items' => [
                ['item_description' => 'Item A', 'quantity' => 1, 'rate' => 1000, 'tax_rate' => 18],
            ],
        ]);

        $response->assertStatus(201);
        $voucher = $response->json('data');
        $this->assertSame('sales', $voucher['voucher_type']);
        $this->assertSame('SAL-2026-0001', $voucher['voucher_no']);
        $this->assertSame('1000.00', (string) $voucher['taxable_total']);
        $this->assertSame('180.00', (string) $voucher['tax_total']);
        $this->assertSame('1180.00', (string) $voucher['amount']);
        $this->assertCount(1, $voucher['items']);
        $this->assertSame('1000.00', (string) $voucher['items'][0]['taxable_amount']);
        $this->assertSame('90.00', (string) $voucher['items'][0]['cgst_amount']);
        $this->assertSame('90.00', (string) $voucher['items'][0]['sgst_amount']);

        // Customer Dr, Sales Cr, CGST Cr, SGST Cr.
        $this->assertCount(4, $voucher['entries']);
        $this->assertSame('Dr', $this->entryFor($voucher['entries'], $customer->id)['entry_type']);
        $this->assertSame('Cr', $this->entryFor($voucher['entries'], $l['sales']->id)['entry_type']);

        $this->assertDatabaseHas('vouchers', [
            'id' => $voucher['id'],
            'taxable_total' => 1000,
            'tax_total' => 180,
            'gst_type' => 'intra',
        ]);
        $this->assertDatabaseHas('voucher_items', [
            'voucher_id' => $voucher['id'],
            'line_no' => 1,
            'taxable_amount' => 1000,
            'cgst_amount' => 90,
            'sgst_amount' => 90,
        ]);
    }

    public function test_inter_state_sales_posts_igst_instead_of_cgst_sgst(): void
    {
        $this->actingAs($this->superAdmin());
        $customer = $this->customer();
        $l = $this->seedDefaults();

        $response = $this->postJson('/api/v1/vouchers', [
            'voucher_type' => 'sales',
            'voucher_date' => '2026-05-01',
            'party_ledger_id' => $customer->id,
            'gst_type' => 'inter',
            'items' => [
                ['item_description' => 'Item A', 'quantity' => 1, 'rate' => 1000, 'tax_rate' => 18],
            ],
        ]);

        $response->assertStatus(201);
        $voucher = $response->json('data');
        $this->assertSame('180.00', (string) $voucher['items'][0]['igst_amount']);
        $this->assertSame('0.00', (string) $voucher['items'][0]['cgst_amount']);

        $this->assertCount(3, $voucher['entries']); // customer + sales + igst
        $this->assertSame('Cr', $this->entryFor($voucher['entries'], $l['igst']->id)['entry_type']);
        $this->assertNull($this->entryFor($voucher['entries'], $l['cgst']->id));
    }

    public function test_it_applies_line_discounts_before_tax(): void
    {
        $this->actingAs($this->superAdmin());
        $customer = $this->customer();
        $this->seedDefaults();

        $response = $this->postJson('/api/v1/vouchers', [
            'voucher_type' => 'sales',
            'voucher_date' => '2026-05-01',
            'party_ledger_id' => $customer->id,
            'items' => [
                [
                    'item_description' => 'Bulk',
                    'quantity' => 10,
                    'rate' => 100,
                    'discount_percent' => 10,
                    'tax_rate' => 18,
                ],
            ],
        ]);

        $response->assertStatus(201);
        $voucher = $response->json('data');
        $this->assertSame('100.00', (string) $voucher['discount_total']);
        $this->assertSame('900.00', (string) $voucher['taxable_total']);
        $this->assertSame('162.00', (string) $voucher['tax_total']);
        $this->assertSame('1062.00', (string) $voucher['amount']);
    }

    public function test_round_off_posts_to_the_round_off_ledger_and_keeps_it_balanced(): void
    {
        $this->actingAs($this->superAdmin());
        $customer = $this->customer();
        $l = $this->seedDefaults();

        $response = $this->postJson('/api/v1/vouchers', [
            'voucher_type' => 'sales',
            'voucher_date' => '2026-05-01',
            'party_ledger_id' => $customer->id,
            'round_off' => -0.40,
            'items' => [
                ['item_description' => 'Item A', 'quantity' => 1, 'rate' => 1000, 'tax_rate' => 0],
            ],
        ]);

        $response->assertStatus(201);
        $voucher = $response->json('data');
        $this->assertSame('999.60', (string) $voucher['amount']);
        $this->assertCount(3, $voucher['entries']);
        $roundOff = $this->entryFor($voucher['entries'], $l['round']->id);
        $this->assertSame('Dr', $roundOff['entry_type']);
        $this->assertSame('0.40', (string) $roundOff['amount']);
    }

    /* ------------------------------ purchase ----------------------------- */

    public function test_it_creates_a_purchase_voucher(): void
    {
        $this->actingAs($this->superAdmin());
        $supplier = $this->supplier();
        $l = $this->seedDefaults();

        $response = $this->postJson('/api/v1/vouchers', [
            'voucher_type' => 'purchase',
            'voucher_date' => '2026-05-02',
            'party_ledger_id' => $supplier->id,
            'items' => [
                ['item_description' => 'Raw', 'quantity' => 2, 'rate' => 500, 'tax_rate' => 5],
            ],
        ]);

        $response->assertStatus(201);
        $voucher = $response->json('data');
        $this->assertSame('purchase', $voucher['voucher_type']);
        $this->assertSame('PUR-2026-0001', $voucher['voucher_no']);
        $this->assertSame('1050.00', (string) $voucher['amount']); // 1000 + 50

        // Supplier Cr, Purchase Dr, CGST Dr, SGST Dr.
        $this->assertSame('Cr', $this->entryFor($voucher['entries'], $supplier->id)['entry_type']);
        $this->assertSame('Dr', $this->entryFor($voucher['entries'], $l['purchase']->id)['entry_type']);
    }

    /* ---------------------------- credit / debit ------------------------- */

    public function test_it_creates_a_credit_note_reversing_a_sale(): void
    {
        $this->actingAs($this->superAdmin());
        $customer = $this->customer();
        $l = $this->seedDefaults();

        $response = $this->postJson('/api/v1/vouchers', [
            'voucher_type' => 'credit-note',
            'voucher_date' => '2026-05-03',
            'party_ledger_id' => $customer->id,
            'items' => [
                ['item_description' => 'Returned', 'quantity' => 1, 'rate' => 1000, 'tax_rate' => 18],
            ],
        ]);

        $response->assertStatus(201);
        $voucher = $response->json('data');
        $this->assertSame('CRN-2026-0001', $voucher['voucher_no']);
        $this->assertSame('1180.00', (string) $voucher['amount']);

        // Customer Cr, Sales Dr, CGST Dr, SGST Dr.
        $this->assertSame('Cr', $this->entryFor($voucher['entries'], $customer->id)['entry_type']);
        $this->assertSame('Dr', $this->entryFor($voucher['entries'], $l['sales']->id)['entry_type']);
    }

    public function test_it_creates_a_debit_note_reversing_a_purchase(): void
    {
        $this->actingAs($this->superAdmin());
        $supplier = $this->supplier();
        $l = $this->seedDefaults();

        $response = $this->postJson('/api/v1/vouchers', [
            'voucher_type' => 'debit-note',
            'voucher_date' => '2026-05-04',
            'party_ledger_id' => $supplier->id,
            'items' => [
                ['item_description' => 'Returned', 'quantity' => 1, 'rate' => 1000, 'tax_rate' => 18],
            ],
        ]);

        $response->assertStatus(201);
        $voucher = $response->json('data');
        $this->assertSame('DBN-2026-0001', $voucher['voucher_no']);

        // Supplier Dr, Purchase Cr, CGST Cr, SGST Cr.
        $this->assertSame('Dr', $this->entryFor($voucher['entries'], $supplier->id)['entry_type']);
        $this->assertSame('Cr', $this->entryFor($voucher['entries'], $l['purchase']->id)['entry_type']);
    }

    /* ------------------------------ validation --------------------------- */

    public function test_it_rejects_a_party_ledger_of_the_wrong_type(): void
    {
        $this->actingAs($this->superAdmin());
        $supplier = $this->supplier();
        $this->seedDefaults();

        $this->postJson('/api/v1/vouchers', [
            'voucher_type' => 'sales', // needs a customer
            'voucher_date' => '2026-05-01',
            'party_ledger_id' => $supplier->id,
            'items' => [
                ['item_description' => 'Item A', 'quantity' => 1, 'rate' => 100, 'tax_rate' => 0],
            ],
        ])->assertStatus(422);
    }

    public function test_it_requires_the_default_base_ledger_to_exist(): void
    {
        $this->actingAs($this->superAdmin());
        $customer = $this->customer();
        // Deliberately do NOT seed the 'Sales' ledger.

        $this->postJson('/api/v1/vouchers', [
            'voucher_type' => 'sales',
            'voucher_date' => '2026-05-01',
            'party_ledger_id' => $customer->id,
            'items' => [
                ['item_description' => 'Item A', 'quantity' => 1, 'rate' => 100, 'tax_rate' => 0],
            ],
        ])->assertStatus(422);
    }

    public function test_it_rejects_an_empty_item_list(): void
    {
        $this->actingAs($this->superAdmin());
        $customer = $this->customer();
        $this->seedDefaults();

        $this->postJson('/api/v1/vouchers', [
            'voucher_type' => 'sales',
            'voucher_date' => '2026-05-01',
            'party_ledger_id' => $customer->id,
            'items' => [],
        ])->assertStatus(422);
    }

    public function test_it_rejects_a_zero_total(): void
    {
        $this->actingAs($this->superAdmin());
        $customer = $this->customer();
        $this->seedDefaults();

        $this->postJson('/api/v1/vouchers', [
            'voucher_type' => 'sales',
            'voucher_date' => '2026-05-01',
            'party_ledger_id' => $customer->id,
            'items' => [
                ['item_description' => 'Freebie', 'quantity' => 1, 'rate' => 0, 'tax_rate' => 0],
            ],
        ])->assertStatus(422);
    }

    /* -------------------------- update and delete ------------------------ */

    public function test_it_updates_an_item_voucher_and_rebuilds_its_items(): void
    {
        $this->actingAs($this->superAdmin());
        $customer = $this->customer();
        $this->seedDefaults();

        $created = $this->postJson('/api/v1/vouchers', [
            'voucher_type' => 'sales',
            'voucher_date' => '2026-05-01',
            'party_ledger_id' => $customer->id,
            'items' => [
                ['item_description' => 'Item A', 'quantity' => 1, 'rate' => 1000, 'tax_rate' => 18],
            ],
        ])->json('data');

        $response = $this->putJson("/api/v1/vouchers/{$created['id']}", [
            'voucher_type' => 'sales',
            'voucher_date' => '2026-05-01',
            'party_ledger_id' => $customer->id,
            'items' => [
                ['item_description' => 'Item B', 'quantity' => 2, 'rate' => 500, 'tax_rate' => 18],
                ['item_description' => 'Item C', 'quantity' => 1, 'rate' => 100, 'tax_rate' => 18],
            ],
        ]);

        $response->assertOk();
        $voucher = $response->json('data');
        $this->assertSame('1100.00', (string) $voucher['taxable_total']);
        $this->assertSame('1298.00', (string) $voucher['amount']);
        $this->assertSame($created['voucher_no'], $voucher['voucher_no']);
        $this->assertSame(2, Voucher::find($created['id'])->items()->count());
        $this->assertSame(4, Voucher::find($created['id'])->entries()->count());
        $this->assertDatabaseMissing('voucher_items', ['voucher_id' => $created['id'], 'item_description' => 'Item A']);
    }

    public function test_deleting_an_item_voucher_removes_its_items(): void
    {
        $this->actingAs($this->superAdmin());
        $customer = $this->customer();
        $this->seedDefaults();

        $created = $this->postJson('/api/v1/vouchers', [
            'voucher_type' => 'sales',
            'voucher_date' => '2026-05-01',
            'party_ledger_id' => $customer->id,
            'items' => [
                ['item_description' => 'Item A', 'quantity' => 1, 'rate' => 1000, 'tax_rate' => 18],
            ],
        ])->json('data');

        $this->deleteJson("/api/v1/vouchers/{$created['id']}")->assertStatus(204);

        $this->assertDatabaseMissing('vouchers', ['id' => $created['id']]);
        $this->assertDatabaseMissing('voucher_items', ['voucher_id' => $created['id']]);
        $this->assertDatabaseMissing('voucher_entries', ['voucher_id' => $created['id']]);
    }

    /* ------------------------------ metadata ----------------------------- */

    public function test_meta_lists_the_default_ledgers(): void
    {
        $this->actingAs($this->superAdmin());
        $l = $this->seedDefaults();

        $response = $this->getJson('/api/v1/vouchers/meta');
        $response->assertOk();
        $this->assertSame($l['sales']->id, $response->json('data.defaults.sales_ledger.id'));
        $this->assertSame($l['cgst']->id, $response->json('data.defaults.cgst_ledger.id'));
        $this->assertContains(18, $response->json('data.gst_rates'));
    }

    /* ----------------------------- outstanding --------------------------- */

    public function test_outstanding_includes_a_sales_voucher_as_a_bill(): void
    {
        $this->actingAs($this->superAdmin());
        $customer = $this->customer();
        $this->seedDefaults();

        $voucher = $this->postJson('/api/v1/vouchers', [
            'voucher_type' => 'sales',
            'voucher_date' => '2026-05-01',
            'party_ledger_id' => $customer->id,
            'items' => [
                ['item_description' => 'Item A', 'quantity' => 1, 'rate' => 1000, 'tax_rate' => 18],
            ],
        ])->json('data');

        $outstanding = $this->getJson("/api/v1/vouchers/party-outstanding/{$customer->id}");
        $outstanding->assertOk();
        $this->assertSame(1180.0, (float) $outstanding->json('data.total_outstanding'));
        $this->assertSame('sales-voucher', $outstanding->json('data.bills.0.bill_type'));
        $this->assertSame($voucher['voucher_no'], $outstanding->json('data.bills.0.bill_name'));
    }

    /* ---------------------------- permissions ---------------------------- */

    public function test_a_user_without_the_permission_cannot_create_a_sales_voucher(): void
    {
        $user = $this->userWithPermissions(['sales-vouchers.view']);
        $customer = $this->customer();
        $this->seedDefaults();

        $this->actingAs($user)->postJson('/api/v1/vouchers', [
            'voucher_type' => 'sales',
            'voucher_date' => '2026-05-01',
            'party_ledger_id' => $customer->id,
            'items' => [
                ['item_description' => 'Item A', 'quantity' => 1, 'rate' => 100, 'tax_rate' => 0],
            ],
        ])->assertStatus(403);
    }

    public function test_a_user_with_the_permission_can_create_a_sales_voucher(): void
    {
        $user = $this->userWithPermissions(['sales-vouchers.view', 'sales-vouchers.create']);
        $customer = $this->customer();
        $this->seedDefaults();

        $this->actingAs($user)->postJson('/api/v1/vouchers', [
            'voucher_type' => 'sales',
            'voucher_date' => '2026-05-01',
            'party_ledger_id' => $customer->id,
            'items' => [
                ['item_description' => 'Item A', 'quantity' => 1, 'rate' => 100, 'tax_rate' => 0],
            ],
        ])->assertStatus(201);
    }

    public function test_a_purchase_voucher_requires_the_purchase_module_not_sales(): void
    {
        $user = $this->userWithPermissions(['sales-vouchers.view', 'sales-vouchers.create']);
        $supplier = $this->supplier();
        $this->seedDefaults();

        $this->actingAs($user)->postJson('/api/v1/vouchers', [
            'voucher_type' => 'purchase',
            'voucher_date' => '2026-05-01',
            'party_ledger_id' => $supplier->id,
            'items' => [
                ['item_description' => 'Raw', 'quantity' => 1, 'rate' => 100, 'tax_rate' => 0],
            ],
        ])->assertStatus(403);
    }
}
