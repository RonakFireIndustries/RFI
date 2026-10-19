<?php

namespace Tests\Feature;

use App\Models\AccountGroup;
use App\Models\FinancialYear;
use App\Models\LedgerAccount;
use App\Models\User;
use App\Models\Voucher;
use App\Services\Accounting\VoucherService;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Covers the manual bank reconciliation statement (slice 4): opening balance,
 * running book balance, cleared vs in-transit items, the reconcile/unreconcile
 * endpoints and permission enforcement.
 *
 * As with the other accounting suites, only the migrations these endpoints
 * need are run — the full chain is MySQL-specific in places.
 */
class BankReconciliationTest extends TestCase
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
        '2026_10_17_000009_add_reconciliation_to_voucher_entries_table.php',
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

    private function bank(string $name = 'HDFC Bank', float $opening = 0): LedgerAccount
    {
        return $this->ledger($name, 'Assets', [
            'is_bank' => true,
            'opening_balance' => $opening,
            'opening_type' => 'Dr',
            'bank_name' => $name,
        ]);
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

    /**
     * Post a balanced voucher through the engine (setup, no HTTP).
     */
    private function voucher(User $user, string $type, string $date, array $entries, ?int $partyLedgerId = null): Voucher
    {
        return app(VoucherService::class)->create([
            'voucher_type' => $type,
            'voucher_date' => $date,
            'party_ledger_id' => $partyLedgerId,
            'entries' => $entries,
        ], $user->id);
    }

    /** Receipt: money into the bank (Dr Bank). */
    private function deposit(User $user, LedgerAccount $bank, LedgerAccount $party, float $amount, string $date): Voucher
    {
        return $this->voucher($user, 'receipt', $date, [
            ['ledger_id' => $bank->id, 'entry_type' => 'Dr', 'amount' => $amount],
            ['ledger_id' => $party->id, 'entry_type' => 'Cr', 'amount' => $amount],
        ], $party->id);
    }

    /** Payment: money out of the bank (Cr Bank). */
    private function withdraw(User $user, LedgerAccount $bank, LedgerAccount $party, float $amount, string $date): Voucher
    {
        return $this->voucher($user, 'payment', $date, [
            ['ledger_id' => $party->id, 'entry_type' => 'Dr', 'amount' => $amount],
            ['ledger_id' => $bank->id, 'entry_type' => 'Cr', 'amount' => $amount],
        ], $party->id);
    }

    private function entryIdFor(Voucher $voucher, LedgerAccount $ledger): int
    {
        return (int) $voucher->entries()->where('ledger_id', $ledger->id)->value('id');
    }

    /* ------------------------------ statement ---------------------------- */

    public function test_statement_lists_bank_entries_with_a_running_balance(): void
    {
        $admin = $this->superAdmin();
        $bank = $this->bank('HDFC Bank', 1000);
        $customer = $this->customer();
        $supplier = $this->supplier();

        $this->deposit($admin, $bank, $customer, 500, '2026-05-02');
        $this->withdraw($admin, $bank, $supplier, 200, '2026-05-05');

        $response = $this->actingAs($admin)->getJson("/api/v1/bank-reconciliation/{$bank->id}");

        $response->assertOk();
        $this->assertSame(1000.0, (float) $response->json('data.opening_balance'));

        $entries = $response->json('data.entries');
        $this->assertCount(2, $entries);
        $this->assertSame('Dr', $entries[0]['entry_type']);
        $this->assertSame(1500.0, (float) $entries[0]['balance']);
        $this->assertSame('Cr', $entries[1]['entry_type']);
        $this->assertSame(1300.0, (float) $entries[1]['balance']);

        $summary = $response->json('data.summary');
        $this->assertSame(1300.0, (float) $summary['book_balance']);
        $this->assertSame(500.0, (float) $summary['unreconciled_dr']);
        $this->assertSame(200.0, (float) $summary['unreconciled_cr']);
        // Nothing cleared yet: deposits in transit are removed, unpresented
        // cheques added back.
        $this->assertSame(1000.0, (float) $summary['statement_balance']);
    }

    public function test_a_date_range_rolls_earlier_entries_into_the_opening(): void
    {
        $admin = $this->superAdmin();
        $bank = $this->bank('HDFC Bank', 1000);
        $customer = $this->customer();
        $supplier = $this->supplier();

        $this->deposit($admin, $bank, $customer, 500, '2026-05-02');
        $this->withdraw($admin, $bank, $supplier, 200, '2026-05-10');

        $response = $this->actingAs($admin)
            ->getJson("/api/v1/bank-reconciliation/{$bank->id}?from=2026-05-05");

        $response->assertOk();
        $this->assertSame(1500.0, (float) $response->json('data.opening_balance'));
        $this->assertCount(1, $response->json('data.entries'));
        $this->assertSame(1300.0, (float) $response->json('data.summary.book_balance'));
    }

    public function test_opening_credit_balance_reduces_the_book_balance(): void
    {
        $admin = $this->superAdmin();
        $bank = $this->ledger('OD Account', 'Liabilities', [
            'is_bank' => true, 'opening_balance' => 400, 'opening_type' => 'Cr',
        ]);
        $customer = $this->customer();
        $this->deposit($admin, $bank, $customer, 100, '2026-05-02');

        $response = $this->actingAs($admin)->getJson("/api/v1/bank-reconciliation/{$bank->id}");
        $response->assertOk();
        $this->assertSame(-400.0, (float) $response->json('data.opening_balance'));
        $this->assertSame(-300.0, (float) $response->json('data.summary.book_balance'));
    }

    /* ------------------------------ reconcile ---------------------------- */

    public function test_reconciling_an_entry_moves_the_statement_balance(): void
    {
        $admin = $this->superAdmin();
        $bank = $this->bank('HDFC Bank', 1000);
        $customer = $this->customer();
        $supplier = $this->supplier();

        $receipt = $this->deposit($admin, $bank, $customer, 500, '2026-05-02');
        $this->withdraw($admin, $bank, $supplier, 200, '2026-05-05');

        $entryId = $this->entryIdFor($receipt, $bank);

        $this->actingAs($admin)
            ->postJson("/api/v1/bank-reconciliation/{$bank->id}/reconcile", [
                'entry_ids' => [$entryId],
                'reconciled_at' => '2026-05-03',
                'note' => 'Chq cleared',
            ])
            ->assertOk()
            ->assertJsonPath('data.updated', 1);

        $this->assertDatabaseHas('voucher_entries', [
            'id' => $entryId,
            'reconciled_at' => '2026-05-03',
            'reconciled_by' => $admin->id,
            'reconciliation_note' => 'Chq cleared',
        ]);

        $summary = $this->actingAs($admin)
            ->getJson("/api/v1/bank-reconciliation/{$bank->id}")
            ->json('data.summary');

        $this->assertSame(0.0, (float) $summary['unreconciled_dr']);
        $this->assertSame(200.0, (float) $summary['unreconciled_cr']);
        $this->assertSame(1500.0, (float) $summary['statement_balance']);
    }

    public function test_unreconciling_clears_the_stamp(): void
    {
        $admin = $this->superAdmin();
        $bank = $this->bank('HDFC Bank', 0);
        $customer = $this->customer();
        $receipt = $this->deposit($admin, $bank, $customer, 500, '2026-05-02');
        $entryId = $this->entryIdFor($receipt, $bank);

        $this->actingAs($admin)->postJson("/api/v1/bank-reconciliation/{$bank->id}/reconcile", [
            'entry_ids' => [$entryId],
            'reconciled_at' => '2026-05-03',
        ])->assertOk();

        $this->actingAs($admin)->postJson("/api/v1/bank-reconciliation/{$bank->id}/unreconcile", [
            'entry_ids' => [$entryId],
        ])->assertOk()->assertJsonPath('data.updated', 1);

        $this->assertDatabaseHas('voucher_entries', [
            'id' => $entryId,
            'reconciled_at' => null,
            'reconciled_by' => null,
        ]);
    }

    public function test_reconcile_ignores_entries_from_another_ledger(): void
    {
        $admin = $this->superAdmin();
        $bank = $this->bank('HDFC Bank', 0);
        $other = $this->bank('ICICI Bank', 0);
        $customer = $this->customer();

        $receipt = $this->deposit($admin, $bank, $customer, 500, '2026-05-02');
        $entryId = $this->entryIdFor($receipt, $bank);

        $this->actingAs($admin)->postJson("/api/v1/bank-reconciliation/{$other->id}/reconcile", [
            'entry_ids' => [$entryId],
            'reconciled_at' => '2026-05-03',
        ])->assertOk()->assertJsonPath('data.updated', 0);

        $this->assertDatabaseHas('voucher_entries', ['id' => $entryId, 'reconciled_at' => null]);
    }

    public function test_reconcile_requires_a_date_and_at_least_one_entry(): void
    {
        $admin = $this->superAdmin();
        $bank = $this->bank('HDFC Bank', 0);

        $this->actingAs($admin)->postJson("/api/v1/bank-reconciliation/{$bank->id}/reconcile", [
            'entry_ids' => [],
        ])->assertStatus(422);

        $this->actingAs($admin)->postJson("/api/v1/bank-reconciliation/{$bank->id}/reconcile", [
            'entry_ids' => [1],
        ])->assertStatus(422);
    }

    /* -------------------------------- index ------------------------------ */

    public function test_index_lists_only_bank_ledgers_with_their_balances(): void
    {
        $admin = $this->superAdmin();
        $bankA = $this->bank('HDFC Bank', 1000);
        $bankB = $this->bank('ICICI Bank', 0);
        $this->customer(); // not a bank

        $response = $this->actingAs($admin)->getJson('/api/v1/bank-reconciliation');
        $response->assertOk();

        $ledgers = collect($response->json('data'));
        $this->assertCount(2, $ledgers);
        $this->assertEqualsCanonicalizing(
            [$bankA->id, $bankB->id],
            $ledgers->pluck('id')->all()
        );
        $this->assertSame(1000.0, (float) $ledgers->firstWhere('id', $bankA->id)['book_balance']);
    }

    /* ----------------------------- validation ---------------------------- */

    public function test_only_bank_ledgers_can_be_reconciled(): void
    {
        $admin = $this->superAdmin();
        $customer = $this->customer();

        $this->actingAs($admin)
            ->getJson("/api/v1/bank-reconciliation/{$customer->id}")
            ->assertStatus(422);
    }

    /* ---------------------------- permissions ---------------------------- */

    public function test_a_user_without_the_permission_cannot_view_a_statement(): void
    {
        $user = $this->userWithPermissions(['bank-reconciliation.view', 'bank-reconciliation.update']);
        $bank = $this->bank('HDFC Bank', 0);

        // Sanity: the granted user can.
        $this->actingAs($user)->getJson("/api/v1/bank-reconciliation/{$bank->id}")->assertOk();

        $stranger = User::factory()->create();
        $this->actingAs($stranger)->getJson("/api/v1/bank-reconciliation/{$bank->id}")->assertStatus(403);
        $this->actingAs($stranger)->getJson('/api/v1/bank-reconciliation')->assertStatus(403);
    }

    public function test_reconciling_requires_the_update_permission(): void
    {
        $viewer = $this->userWithPermissions(['bank-reconciliation.view']);
        $admin = $this->superAdmin();
        $bank = $this->bank('HDFC Bank', 0);
        $customer = $this->customer();
        $receipt = $this->deposit($admin, $bank, $customer, 100, '2026-05-02');
        $entryId = $this->entryIdFor($receipt, $bank);

        $this->actingAs($viewer)->getJson("/api/v1/bank-reconciliation/{$bank->id}")->assertOk();

        $this->actingAs($viewer)->postJson("/api/v1/bank-reconciliation/{$bank->id}/reconcile", [
            'entry_ids' => [$entryId],
            'reconciled_at' => '2026-05-03',
        ])->assertStatus(403);
    }
}
