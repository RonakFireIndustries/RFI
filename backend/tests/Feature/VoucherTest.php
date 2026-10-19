<?php

namespace Tests\Feature;

use App\Models\AccountGroup;
use App\Models\FinancialYear;
use App\Models\LedgerAccount;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Covers the double-entry voucher engine (slice 2): Contra, Payment, Receipt
 * and Journal, including balance validation, per-type numbering, bill-wise
 * allocations and permission enforcement.
 *
 * As with AccountingTest, only the migrations these endpoints need are run —
 * the full chain is MySQL-specific in places.
 */
class VoucherTest extends TestCase
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

    /* ------------------------------ journal ------------------------------ */

    public function test_it_creates_a_balanced_journal_voucher(): void
    {
        $this->actingAs($this->superAdmin());

        $rent = $this->ledger('Rent', 'Expenses');
        $cash = $this->ledger('Cash', 'Assets', ['is_cash' => true]);

        $response = $this->postJson('/api/v1/vouchers', [
            'voucher_type' => 'journal',
            'voucher_date' => '2026-05-10',
            'narration' => 'Rent for May',
            'entries' => [
                ['ledger_id' => $rent->id, 'entry_type' => 'Dr', 'amount' => 1500],
                ['ledger_id' => $cash->id, 'entry_type' => 'Cr', 'amount' => 1500],
            ],
        ]);

        $response->assertStatus(201);
        $this->assertSame('journal', $response->json('data.voucher_type'));
        $this->assertSame('JRNL-2026-0001', $response->json('data.voucher_no'));
        $this->assertSame('1500.00', $response->json('data.amount'));
        $this->assertCount(2, $response->json('data.entries'));
        $this->assertDatabaseHas('vouchers', ['voucher_type' => 'journal', 'amount' => 1500]);
    }

    public function test_it_rejects_an_unbalanced_voucher(): void
    {
        $this->actingAs($this->superAdmin());

        $a = $this->ledger('Rent', 'Expenses');
        $b = $this->ledger('Cash', 'Assets');

        $this->postJson('/api/v1/vouchers', [
            'voucher_type' => 'journal',
            'voucher_date' => '2026-05-10',
            'entries' => [
                ['ledger_id' => $a->id, 'entry_type' => 'Dr', 'amount' => 1500],
                ['ledger_id' => $b->id, 'entry_type' => 'Cr', 'amount' => 1200],
            ],
        ])->assertStatus(422);
    }

    public function test_it_rejects_a_single_line_voucher(): void
    {
        $this->actingAs($this->superAdmin());

        $a = $this->ledger('Rent', 'Expenses');

        $this->postJson('/api/v1/vouchers', [
            'voucher_type' => 'journal',
            'voucher_date' => '2026-05-10',
            'entries' => [
                ['ledger_id' => $a->id, 'entry_type' => 'Dr', 'amount' => 1500],
            ],
        ])->assertStatus(422);
    }

    public function test_journal_numbers_are_sequential_within_a_year(): void
    {
        $this->actingAs($this->superAdmin());

        $a = $this->ledger('Rent', 'Expenses');
        $b = $this->ledger('Cash', 'Assets');

        $payload = fn () => [
            'voucher_type' => 'journal',
            'voucher_date' => '2026-05-10',
            'entries' => [
                ['ledger_id' => $a->id, 'entry_type' => 'Dr', 'amount' => 100],
                ['ledger_id' => $b->id, 'entry_type' => 'Cr', 'amount' => 100],
            ],
        ];

        $this->postJson('/api/v1/vouchers', $payload())->assertJsonPath('data.voucher_no', 'JRNL-2026-0001');
        $this->postJson('/api/v1/vouchers', $payload())->assertJsonPath('data.voucher_no', 'JRNL-2026-0002');
    }

    /* ------------------------------- contra ------------------------------ */

    public function test_it_records_a_contra_between_cash_and_bank(): void
    {
        $this->actingAs($this->superAdmin());

        $cash = $this->ledger('Cash', 'Assets', ['is_cash' => true]);
        $bank = $this->ledger('HDFC', 'Assets', ['is_bank' => true]);

        $response = $this->postJson('/api/v1/vouchers', [
            'voucher_type' => 'contra',
            'voucher_date' => '2026-05-11',
            'entries' => [
                ['ledger_id' => $bank->id, 'entry_type' => 'Dr', 'amount' => 5000],
                ['ledger_id' => $cash->id, 'entry_type' => 'Cr', 'amount' => 5000],
            ],
        ]);

        $response->assertStatus(201);
        $this->assertSame('CONTRA-2026-0001', $response->json('data.voucher_no'));
    }

    /* -------------------------- payment / bills -------------------------- */

    public function test_it_records_a_payment_with_bill_wise_allocations(): void
    {
        $this->actingAs($this->superAdmin());

        $supplier = $this->ledger('Supplier A', 'Liabilities', [
            'is_party_group' => true, 'party_type' => 'supplier',
        ]);
        $bank = $this->ledger('HDFC', 'Assets', ['is_bank' => true]);

        $response = $this->postJson('/api/v1/vouchers', [
            'voucher_type' => 'payment',
            'voucher_date' => '2026-06-01',
            'reference_no' => 'CHQ-001',
            'party_ledger_id' => $supplier->id,
            'entries' => [
                ['ledger_id' => $supplier->id, 'entry_type' => 'Dr', 'amount' => 10000],
                ['ledger_id' => $bank->id, 'entry_type' => 'Cr', 'amount' => 10000],
            ],
            'bill_allocations' => [
                ['bill_type' => 'opening', 'bill_name' => 'Opening Balance', 'amount' => 6000],
                ['bill_type' => 'advance', 'bill_name' => 'Advance', 'amount' => 4000],
            ],
        ]);

        $response->assertStatus(201);
        $this->assertSame('PMT-2026-0001', $response->json('data.voucher_no'));
        $this->assertSame($supplier->id, $response->json('data.party_ledger_id'));
        $this->assertCount(2, $response->json('data.bill_allocations'));
        $this->assertDatabaseHas('voucher_bill_allocations', [
            'bill_type' => 'opening',
            'amount' => 6000,
        ]);
    }

    public function test_it_rejects_allocations_greater_than_the_voucher_amount(): void
    {
        $this->actingAs($this->superAdmin());

        $supplier = $this->ledger('Supplier A', 'Liabilities', [
            'is_party_group' => true, 'party_type' => 'supplier',
        ]);
        $bank = $this->ledger('HDFC', 'Assets', ['is_bank' => true]);

        $this->postJson('/api/v1/vouchers', [
            'voucher_type' => 'payment',
            'voucher_date' => '2026-06-01',
            'party_ledger_id' => $supplier->id,
            'entries' => [
                ['ledger_id' => $supplier->id, 'entry_type' => 'Dr', 'amount' => 1000],
                ['ledger_id' => $bank->id, 'entry_type' => 'Cr', 'amount' => 1000],
            ],
            'bill_allocations' => [
                ['bill_type' => 'opening', 'amount' => 1500],
            ],
        ])->assertStatus(422);
    }

    public function test_a_journal_voucher_without_allocations_is_allowed(): void
    {
        $this->actingAs($this->superAdmin());

        $customer = $this->ledger('Customer X', 'Assets', [
            'is_party_group' => true, 'party_type' => 'customer',
        ]);
        $sales = $this->ledger('Sales', 'Income');

        $this->postJson('/api/v1/vouchers', [
            'voucher_type' => 'journal',
            'voucher_date' => '2026-06-02',
            'party_ledger_id' => $customer->id,
            'entries' => [
                ['ledger_id' => $customer->id, 'entry_type' => 'Dr', 'amount' => 500],
                ['ledger_id' => $sales->id, 'entry_type' => 'Cr', 'amount' => 500],
            ],
        ])->assertStatus(201);
    }

    /* ------------------------- update and delete ------------------------- */

    public function test_a_voucher_can_be_updated_and_its_entries_rebuilt(): void
    {
        $this->actingAs($this->superAdmin());

        $a = $this->ledger('Rent', 'Expenses');
        $b = $this->ledger('Cash', 'Assets');
        $c = $this->ledger('Salary', 'Expenses');

        $created = $this->postJson('/api/v1/vouchers', [
            'voucher_type' => 'journal',
            'voucher_date' => '2026-05-10',
            'entries' => [
                ['ledger_id' => $a->id, 'entry_type' => 'Dr', 'amount' => 1000],
                ['ledger_id' => $b->id, 'entry_type' => 'Cr', 'amount' => 1000],
            ],
        ])->json('data');

        $response = $this->putJson("/api/v1/vouchers/{$created['id']}", [
            'voucher_type' => 'journal',
            'voucher_date' => '2026-05-10',
            'entries' => [
                ['ledger_id' => $c->id, 'entry_type' => 'Dr', 'amount' => 2000],
                ['ledger_id' => $b->id, 'entry_type' => 'Cr', 'amount' => 2000],
            ],
        ]);

        $response->assertOk();
        $this->assertSame('2000.00', $response->json('data.amount'));
        $this->assertSame($created['voucher_no'], $response->json('data.voucher_no'));
        $this->assertSame(2, Voucher::find($created['id'])->entries()->count());
        $this->assertDatabaseMissing('voucher_entries', ['ledger_id' => $a->id]);
    }

    public function test_a_voucher_delete_removes_its_lines_and_allocations(): void
    {
        $this->actingAs($this->superAdmin());

        $supplier = $this->ledger('Supplier A', 'Liabilities', [
            'is_party_group' => true, 'party_type' => 'supplier',
        ]);
        $bank = $this->ledger('HDFC', 'Assets', ['is_bank' => true]);

        $created = $this->postJson('/api/v1/vouchers', [
            'voucher_type' => 'payment',
            'voucher_date' => '2026-06-01',
            'party_ledger_id' => $supplier->id,
            'entries' => [
                ['ledger_id' => $supplier->id, 'entry_type' => 'Dr', 'amount' => 700],
                ['ledger_id' => $bank->id, 'entry_type' => 'Cr', 'amount' => 700],
            ],
            'bill_allocations' => [
                ['bill_type' => 'opening', 'amount' => 700],
            ],
        ])->json('data');

        $this->deleteJson("/api/v1/vouchers/{$created['id']}")->assertStatus(204);

        $this->assertDatabaseMissing('vouchers', ['id' => $created['id']]);
        $this->assertDatabaseMissing('voucher_entries', ['voucher_id' => $created['id']]);
        $this->assertDatabaseMissing('voucher_bill_allocations', ['voucher_id' => $created['id']]);
    }

    /* ---------------------------- outstanding ---------------------------- */

    public function test_party_outstanding_lists_opening_balance_and_applies_allocations(): void
    {
        $this->actingAs($this->superAdmin());

        $supplier = $this->ledger('Supplier A', 'Liabilities', [
            'is_party_group' => true, 'party_type' => 'supplier',
        ]);
        $supplier->update(['opening_balance' => 5000, 'opening_type' => 'Cr']);
        $bank = $this->ledger('HDFC', 'Assets', ['is_bank' => true]);

        // Before any payment the full opening balance is outstanding.
        $before = $this->getJson("/api/v1/vouchers/party-outstanding/{$supplier->id}");
        $before->assertOk();
        $this->assertSame(5000.0, (float) $before->json('data.total_outstanding'));

        // A payment settling 2000 of it leaves 3000.
        $this->postJson('/api/v1/vouchers', [
            'voucher_type' => 'payment',
            'voucher_date' => '2026-06-01',
            'party_ledger_id' => $supplier->id,
            'entries' => [
                ['ledger_id' => $supplier->id, 'entry_type' => 'Dr', 'amount' => 2000],
                ['ledger_id' => $bank->id, 'entry_type' => 'Cr', 'amount' => 2000],
            ],
            'bill_allocations' => [
                ['bill_type' => 'opening', 'amount' => 2000],
            ],
        ])->assertStatus(201);

        $after = $this->getJson("/api/v1/vouchers/party-outstanding/{$supplier->id}");
        $after->assertOk();
        $this->assertSame(3000.0, (float) $after->json('data.total_outstanding'));
    }

    public function test_outstanding_includes_legacy_invoices_and_applies_allocations(): void
    {
        $this->actingAs($this->superAdmin());

        // A stand-in for the legacy invoice register this slice still reads from.
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('invoice_number');
            $table->string('status')->default('Unpaid');
            $table->date('issue_date')->nullable();
            $table->date('due_date')->nullable();
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
        });

        $group = AccountGroup::create([
            'name' => 'Debtors', 'nature' => 'Assets', 'is_primary' => true,
            'is_party_group' => true, 'party_type' => 'customer',
        ]);
        $ledger = LedgerAccount::create([
            'name' => 'Acme Corp', 'group_id' => $group->id,
            'party_type' => 'customer', 'party_id' => 7,
        ]);

        DB::table('invoices')->insert([
            ['customer_id' => 7, 'invoice_number' => 'INV-1', 'status' => 'Unpaid', 'grand_total' => 1000, 'paid_amount' => 0],
            ['customer_id' => 99, 'invoice_number' => 'INV-OTHER', 'status' => 'Unpaid', 'grand_total' => 500, 'paid_amount' => 0],
            ['customer_id' => 7, 'invoice_number' => 'INV-CANCELLED', 'status' => 'Cancelled', 'grand_total' => 300, 'paid_amount' => 0],
        ]);

        // Only the party's own, non-cancelled invoice counts.
        $before = $this->getJson("/api/v1/vouchers/party-outstanding/{$ledger->id}");
        $before->assertOk();
        $this->assertSame(1000.0, (float) $before->json('data.total_outstanding'));
        $this->assertSame('invoice', $before->json('data.bills.0.bill_type'));

        $bank = $this->ledger('Cash', 'Assets', ['is_cash' => true]);

        $this->postJson('/api/v1/vouchers', [
            'voucher_type' => 'receipt',
            'voucher_date' => '2026-06-05',
            'party_ledger_id' => $ledger->id,
            'entries' => [
                ['ledger_id' => $bank->id, 'entry_type' => 'Dr', 'amount' => 400],
                ['ledger_id' => $ledger->id, 'entry_type' => 'Cr', 'amount' => 400],
            ],
            'bill_allocations' => [
                ['bill_type' => 'invoice', 'bill_id' => 1, 'bill_name' => 'INV-1', 'amount' => 400],
            ],
        ])->assertStatus(201);

        $after = $this->getJson("/api/v1/vouchers/party-outstanding/{$ledger->id}");
        $after->assertOk();
        $this->assertSame(600.0, (float) $after->json('data.total_outstanding'));
        $this->assertSame(400.0, (float) $after->json('data.bills.0.settled'));
    }

    public function test_it_rejects_bill_details_on_a_voucher_type_without_them(): void
    {
        $this->actingAs($this->superAdmin());

        $cash = $this->ledger('Cash', 'Assets', ['is_cash' => true]);
        $bank = $this->ledger('HDFC', 'Assets', ['is_bank' => true]);

        $this->postJson('/api/v1/vouchers', [
            'voucher_type' => 'contra',
            'voucher_date' => '2026-05-11',
            'entries' => [
                ['ledger_id' => $bank->id, 'entry_type' => 'Dr', 'amount' => 100],
                ['ledger_id' => $cash->id, 'entry_type' => 'Cr', 'amount' => 100],
            ],
            'bill_allocations' => [
                ['bill_type' => 'advance', 'amount' => 100],
            ],
        ])->assertStatus(422);
    }

    public function test_an_unknown_voucher_type_is_rejected(): void
    {
        $this->actingAs($this->superAdmin());

        $a = $this->ledger('Rent', 'Expenses');
        $b = $this->ledger('Cash', 'Assets');

        $this->postJson('/api/v1/vouchers', [
            'voucher_type' => 'not-a-real-type',
            'voucher_date' => '2026-05-10',
            'entries' => [
                ['ledger_id' => $a->id, 'entry_type' => 'Dr', 'amount' => 100],
                ['ledger_id' => $b->id, 'entry_type' => 'Cr', 'amount' => 100],
            ],
        ])->assertStatus(422);
    }

    /* ---------------------------- permissions ---------------------------- */

    public function test_a_user_without_the_permission_cannot_create_a_voucher(): void
    {
        $user = $this->userWithPermissions(['journal.view']);
        $a = $this->ledger('Rent', 'Expenses');
        $b = $this->ledger('Cash', 'Assets');

        $this->actingAs($user)->postJson('/api/v1/vouchers', [
            'voucher_type' => 'journal',
            'voucher_date' => '2026-05-10',
            'entries' => [
                ['ledger_id' => $a->id, 'entry_type' => 'Dr', 'amount' => 100],
                ['ledger_id' => $b->id, 'entry_type' => 'Cr', 'amount' => 100],
            ],
        ])->assertStatus(403);
    }

    public function test_the_register_only_returns_types_the_user_can_view(): void
    {
        $user = $this->userWithPermissions(['journal.view']);
        $this->actingAs($user);

        $a = $this->ledger('Rent', 'Expenses');
        $b = $this->ledger('Cash', 'Assets');

        // A journal the user may see.
        $this->actingAs($this->superAdmin())->postJson('/api/v1/vouchers', [
            'voucher_type' => 'journal',
            'voucher_date' => '2026-05-10',
            'entries' => [
                ['ledger_id' => $a->id, 'entry_type' => 'Dr', 'amount' => 100],
                ['ledger_id' => $b->id, 'entry_type' => 'Cr', 'amount' => 100],
            ],
        ])->assertStatus(201);

        // A contra the user may not.
        $this->actingAs($this->superAdmin())->postJson('/api/v1/vouchers', [
            'voucher_type' => 'contra',
            'voucher_date' => '2026-05-10',
            'entries' => [
                ['ledger_id' => $b->id, 'entry_type' => 'Dr', 'amount' => 100],
                ['ledger_id' => $a->id, 'entry_type' => 'Cr', 'amount' => 100],
            ],
        ])->assertStatus(201);

        $list = $this->actingAs($user)->getJson('/api/v1/vouchers');
        $list->assertOk();
        $types = collect($list->json('data'))->pluck('voucher_type')->unique()->values();
        $this->assertEquals(['journal'], $types->all());
    }

    public function test_a_user_cannot_view_a_type_they_are_not_granted(): void
    {
        $user = $this->userWithPermissions(['journal.view']);

        $this->actingAs($user)
            ->getJson('/api/v1/vouchers?voucher_type=contra')
            ->assertStatus(403);
    }
}
