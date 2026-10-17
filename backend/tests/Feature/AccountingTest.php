<?php

namespace Tests\Feature;

use App\Models\AccountGroup;
use App\Models\FinancialYear;
use App\Models\LedgerAccount;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Covers the accounting foundation (slice 1): the chart of accounts
 * (groups + ledgers) and financial years, including permission enforcement and
 * the guards that protect the seeded defaults.
 *
 * The application's full migration chain is MySQL-specific in places
 * (SHOW KEYS / ALTER ... MODIFY), so — as BuildingConversionTest does — only the
 * migrations these endpoints actually need are run against the SQLite test
 * database.
 */
class AccountingTest extends TestCase
{
    /** Migrations required by the accounting endpoints and auth/permissions. */
    private const MIGRATIONS = [
        '0001_01_01_000000_create_users_table.php',
        '2026_05_30_083453_create_permission_tables.php',
        '2026_08_27_000000_add_is_super_admin_to_users_table.php',
        '2026_10_17_000001_create_account_groups_table.php',
        '2026_10_17_000002_create_ledger_accounts_table.php',
        '2026_10_17_000003_create_financial_years_table.php',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        foreach (self::MIGRATIONS as $file) {
            $migration = require __DIR__ . '/../../database/migrations/' . $file;
            $migration->up();
        }
    }

    private function superAdmin(): User
    {
        return User::factory()->create(['is_super_admin' => true]);
    }

    /** A plain user who holds the listed canonical permissions. */
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

    /* --------------------------- account groups --------------------------- */

    public function test_it_creates_and_lists_account_groups(): void
    {
        $this->actingAs($this->superAdmin());

        $response = $this->postJson('/api/v1/account-groups', [
            'name' => 'Test Assets',
            'nature' => 'Assets',
            'is_primary' => true,
        ]);

        $response->assertStatus(201);
        $this->assertSame('Test Assets', $response->json('data.name'));

        $list = $this->getJson('/api/v1/account-groups');
        $list->assertOk();
        $names = collect($list->json('data'))->pluck('name');
        $this->assertTrue($names->contains('Test Assets'));
    }

    public function test_a_sub_group_inherits_its_nature(): void
    {
        $this->actingAs($this->superAdmin());

        $parent = AccountGroup::create(['name' => 'Cash Group', 'nature' => 'Assets', 'is_primary' => true]);

        $response = $this->postJson('/api/v1/account-groups', [
            'name' => 'Petty Cash',
            'parent_id' => $parent->id,
        ]);

        $response->assertStatus(201);
        $this->assertSame('Assets', $response->json('data.nature'));
    }

    public function test_a_primary_group_requires_a_nature(): void
    {
        $this->actingAs($this->superAdmin());

        $response = $this->postJson('/api/v1/account-groups', ['name' => 'No Nature']);

        $response->assertStatus(422);
    }

    public function test_it_refuses_to_delete_a_group_that_has_ledgers(): void
    {
        $this->actingAs($this->superAdmin());

        $group = AccountGroup::create(['name' => 'Used Group', 'nature' => 'Assets', 'is_primary' => true]);
        LedgerAccount::create(['name' => 'Lonely Ledger', 'group_id' => $group->id]);

        $this->deleteJson("/api/v1/account-groups/{$group->id}")->assertStatus(422);
        $this->assertDatabaseHas('account_groups', ['name' => 'Used Group']);
    }

    public function test_it_refuses_to_delete_a_seeded_default_group(): void
    {
        $this->actingAs($this->superAdmin());

        $group = AccountGroup::create([
            'name' => 'Default Group', 'nature' => 'Assets', 'is_primary' => true, 'is_system' => true,
        ]);

        $this->deleteJson("/api/v1/account-groups/{$group->id}")->assertStatus(403);
        $this->assertDatabaseHas('account_groups', ['name' => 'Default Group']);
    }

    public function test_a_group_cannot_be_its_own_ancestor(): void
    {
        $this->actingAs($this->superAdmin());

        $group = AccountGroup::create(['name' => 'Loop', 'nature' => 'Assets', 'is_primary' => true]);

        $this->putJson("/api/v1/account-groups/{$group->id}", [
            'name' => 'Loop',
            'nature' => 'Assets',
            'parent_id' => $group->id,
        ])->assertStatus(422);
    }

    /* ------------------------------ ledgers ------------------------------ */

    public function test_it_creates_a_ledger_under_a_group_and_lists_it(): void
    {
        $this->actingAs($this->superAdmin());

        $group = AccountGroup::create(['name' => 'Bank Group', 'nature' => 'Assets', 'is_primary' => true]);

        $response = $this->postJson('/api/v1/ledgers', [
            'name' => 'HDFC Bank',
            'group_id' => $group->id,
            'is_bank' => true,
            'bank_name' => 'HDFC',
            'bank_account_number' => '123456',
            'opening_balance' => 1000,
            'opening_type' => 'Cr',
        ]);

        $response->assertStatus(201);
        $this->assertSame('HDFC Bank', $response->json('data.name'));

        $list = $this->getJson('/api/v1/ledgers?bank_only=1');
        $list->assertOk();
        $this->assertCount(1, $list->json('data'));
        $this->assertSame('Bank Group', $list->json('data.0.group.name'));
    }

    public function test_a_party_group_ledger_earns_its_party_type(): void
    {
        $this->actingAs($this->superAdmin());

        $group = AccountGroup::create([
            'name' => 'Debtors', 'nature' => 'Assets', 'is_primary' => true,
            'is_party_group' => true, 'party_type' => 'customer',
        ]);

        $response = $this->postJson('/api/v1/ledgers', [
            'name' => 'Acme Corp',
            'group_id' => $group->id,
        ]);

        $response->assertStatus(201);
        $this->assertSame('customer', $response->json('data.party_type'));
    }

    public function test_it_refuses_to_delete_a_seeded_default_ledger(): void
    {
        $this->actingAs($this->superAdmin());

        $group = AccountGroup::create(['name' => 'Cash Group', 'nature' => 'Assets', 'is_primary' => true]);
        $ledger = LedgerAccount::create([
            'name' => 'Cash', 'group_id' => $group->id, 'is_cash' => true, 'is_system' => true,
        ]);

        $this->deleteJson("/api/v1/ledgers/{$ledger->id}")->assertStatus(403);
        $this->assertDatabaseHas('ledger_accounts', ['name' => 'Cash']);
    }

    /* -------------------------- financial years -------------------------- */

    public function test_the_first_financial_year_becomes_active_and_only_one_is_active(): void
    {
        $this->actingAs($this->superAdmin());

        $first = $this->postJson('/api/v1/financial-years', [
            'name' => '2026-2027', 'start_date' => '2026-04-01', 'end_date' => '2027-03-31',
        ]);
        $first->assertStatus(201);

        $second = $this->postJson('/api/v1/financial-years', [
            'name' => '2027-2028', 'start_date' => '2027-04-01', 'end_date' => '2028-03-31',
        ]);
        $second->assertStatus(201);

        $this->assertSame(1, FinancialYear::where('is_active', true)->count());

        // The first year is the active one.
        $current = $this->getJson('/api/v1/financial-years/current');
        $current->assertOk();
        $this->assertSame('2026-2027', $current->json('data.name'));

        // Activating the second year flips the single-active invariant.
        $secondId = $second->json('data.id');
        $this->postJson("/api/v1/financial-years/{$secondId}/activate")->assertOk();
        $this->assertSame(1, FinancialYear::where('is_active', true)->count());
        $this->assertSame('2027-2028', $this->getJson('/api/v1/financial-years/current')->json('data.name'));
    }

    public function test_the_active_financial_year_cannot_be_removed(): void
    {
        $this->actingAs($this->superAdmin());

        $year = $this->postJson('/api/v1/financial-years', [
            'name' => '2026-2027', 'start_date' => '2026-04-01', 'end_date' => '2027-03-31',
        ])->json('data');

        $this->deleteJson("/api/v1/financial-years/{$year['id']}")->assertStatus(422);
    }

    /* ----------------------------- permissions --------------------------- */

    public function test_a_user_without_the_permission_is_denied(): void
    {
        $user = $this->userWithPermissions(['account-groups.view']);

        // Can view...
        $this->actingAs($user)->getJson('/api/v1/account-groups')->assertOk();

        // ...but cannot create without account-groups.create.
        $this->actingAs($user)->postJson('/api/v1/account-groups', [
            'name' => 'Nope', 'nature' => 'Assets',
        ])->assertStatus(403);
    }

    public function test_a_user_with_the_permission_can_create(): void
    {
        $user = $this->userWithPermissions(['account-groups.view', 'account-groups.create']);

        $this->actingAs($user)->postJson('/api/v1/account-groups', [
            'name' => 'Allowed', 'nature' => 'Assets',
        ])->assertStatus(201);
    }
}
