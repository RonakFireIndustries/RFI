<?php

namespace Tests\Feature;

use App\Models\AccountGroup;
use App\Models\Customer;
use App\Models\FinancialYear;
use App\Models\LedgerAccount;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Party credit terms drive a bill's due date: voucher date + credit days, a
 * typed due date overriding them, and the overdue flag the Outstanding report
 * derives from it. Until now only legacy invoices carried a due date, so no
 * voucher bill could ever be flagged overdue.
 *
 * As with ItemVoucherTest, only the migrations these endpoints need are run —
 * the full chain is MySQL-specific in places.
 */
class PartyCreditDaysTest extends TestCase
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
     * A customer master with its terms, plus the ledger `accounting:link-parties`
     * would hang off it (party_type + party_id pointing at the master row).
     */
    private function customerWithTerms(?int $creditDays): LedgerAccount
    {
        $customer = Customer::create(['name' => 'Acme Corp', 'credit_days' => $creditDays]);

        return $this->ledger('Acme Corp', 'Assets', [
            'is_party_group' => true,
            'party_type' => 'customer',
            'party_id' => $customer->id,
        ]);
    }

    /** @return array{0: Supplier, 1: LedgerAccount} */
    private function supplierWithTerms(int $creditDays): array
    {
        $supplier = Supplier::create(['name' => 'Vendor Ltd', 'credit_days' => $creditDays]);

        $ledger = $this->ledger('Vendor Ltd', 'Liabilities', [
            'is_party_group' => true,
            'party_type' => 'supplier',
            'party_id' => $supplier->id,
        ]);

        return [$supplier, $ledger];
    }

    /** @return array<string, mixed> the voucher payload an item voucher needs */
    private function salesPayload(LedgerAccount $party, array $extra = []): array
    {
        return array_merge([
            'voucher_type' => 'sales',
            'voucher_date' => '2026-05-01',
            'party_ledger_id' => $party->id,
            'gst_type' => 'intra',
            'items' => [
                ['item_description' => 'Item A', 'quantity' => 2, 'rate' => 500, 'tax_rate' => 18],
            ],
        ], $extra);
    }

    /**
     * The stored due date, read from the model rather than the JSON: a date
     * cast serialises to an instant (midnight IST renders as 18:30Z the day
     * before), so trimming the JSON string would be a day out.
     */
    private function postedDueDate(TestResponse $response): ?string
    {
        $response->assertStatus(201);

        return \App\Models\Voucher::findOrFail($response->json('data.id'))->due_date?->toDateString();
    }

    /* ----------------------------- credit days --------------------------- */

    public function test_credit_days_become_the_sales_bill_due_date(): void
    {
        $this->actingAs($this->superAdmin());
        $this->seedDefaults();
        $party = $this->customerWithTerms(30);

        $due = $this->postedDueDate(
            $this->postJson('/api/v1/vouchers', $this->salesPayload($party))
        );

        // 1 May + 30 credit days.
        $this->assertSame('2026-05-31', $due);
    }

    public function test_supplier_credit_days_become_the_purchase_bill_due_date(): void
    {
        $this->actingAs($this->superAdmin());
        $this->seedDefaults();
        [, $party] = $this->supplierWithTerms(15);

        $due = $this->postedDueDate($this->postJson('/api/v1/vouchers', array_merge(
            $this->salesPayload($party),
            ['voucher_type' => 'purchase']
        )));

        $this->assertSame('2026-05-16', $due);
    }

    public function test_a_party_without_terms_gets_no_due_date(): void
    {
        $this->actingAs($this->superAdmin());
        $this->seedDefaults();
        $party = $this->customerWithTerms(null);

        $due = $this->postedDueDate(
            $this->postJson('/api/v1/vouchers', $this->salesPayload($party))
        );

        $this->assertNull($due);
    }

    public function test_a_typed_due_date_beats_the_party_terms(): void
    {
        $this->actingAs($this->superAdmin());
        $this->seedDefaults();
        $party = $this->customerWithTerms(30);

        $due = $this->postedDueDate($this->postJson(
            '/api/v1/vouchers',
            $this->salesPayload($party, ['due_date' => '2026-06-15'])
        ));

        $this->assertSame('2026-06-15', $due);
    }

    /* ------------------------------ ageing ------------------------------- */

    public function test_the_outstanding_report_ages_the_bill_from_its_due_date(): void
    {
        $admin = $this->superAdmin();
        $this->actingAs($admin);
        $this->seedDefaults();
        $party = $this->customerWithTerms(30);

        $this->postJson('/api/v1/vouchers', $this->salesPayload($party))
            ->assertStatus(201);

        $bill = $this->getJson(
            '/api/v1/accounting-reports/outstanding?side=receivable&as_of=2026-06-15'
            . '&ledger_id=' . $party->id
        )->assertOk()->json('data.bills.0');

        $this->assertSame('2026-05-31', $bill['due_date']);
        $this->assertTrue($bill['overdue']);
        // 15 days past the due date, so it ages from the due date rather than
        // from the 1 May document date.
        $this->assertSame(15, $bill['days']);
    }

    public function test_a_bill_is_not_overdue_before_its_due_date(): void
    {
        $admin = $this->superAdmin();
        $this->actingAs($admin);
        $this->seedDefaults();
        $party = $this->customerWithTerms(30);

        $this->postJson('/api/v1/vouchers', $this->salesPayload($party))
            ->assertStatus(201);

        $bill = $this->getJson(
            '/api/v1/accounting-reports/outstanding?side=receivable&as_of=2026-05-15'
            . '&ledger_id=' . $party->id
        )->assertOk()->json('data.bills.0');

        $this->assertSame('2026-05-31', $bill['due_date']);
        $this->assertFalse($bill['overdue']);
        // Days outstanding count to the as-of date from the due date (-16 …
        // the report clamps at 0) rather than from the document date.
        $this->assertSame(0, $bill['days']);
    }
}
