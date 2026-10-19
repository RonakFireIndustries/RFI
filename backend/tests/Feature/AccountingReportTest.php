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
 * The financial report pack (slice 5): Ledger Statement, Day Book, Outstandings,
 * Profit & Loss, Balance Sheet, Cash/Bank Book and Trial Balance.
 *
 * Only the migrations these endpoints touch are run — the full chain is
 * MySQL-specific in places, as with the other accounting suites.
 */
class AccountingReportTest extends TestCase
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

    /* ------------------------------ fixtures ----------------------------- */

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
            'affects_gross_profit' => $extra['affects_gross_profit'] ?? false,
        ]);

        return LedgerAccount::create(array_merge([
            'name' => $name,
            'group_id' => $group->id,
        ], array_diff_key($extra, [
            'is_party_group' => null,
            'affects_gross_profit' => null,
        ])));
    }

    private function voucher(User $user, string $type, string $date, array $entries, ?int $partyLedgerId = null, array $extra = []): Voucher
    {
        return app(VoucherService::class)->create(array_merge([
            'voucher_type' => $type,
            'voucher_date' => $date,
            'party_ledger_id' => $partyLedgerId,
            'entries' => $entries,
        ], $extra), $user->id);
    }

    /** Dr `$debit` / Cr `$credit`. */
    private function receipt(User $user, LedgerAccount $debit, LedgerAccount $credit, float $amount, string $date): Voucher
    {
        return $this->voucher($user, 'receipt', $date, [
            ['ledger_id' => $debit->id, 'entry_type' => 'Dr', 'amount' => $amount],
            ['ledger_id' => $credit->id, 'entry_type' => 'Cr', 'amount' => $amount],
        ]);
    }

    /** Dr `$debit` / Cr `$credit` as a payment voucher. */
    private function payment(User $user, LedgerAccount $debit, LedgerAccount $credit, float $amount, string $date): Voucher
    {
        return $this->voucher($user, 'payment', $date, [
            ['ledger_id' => $debit->id, 'entry_type' => 'Dr', 'amount' => $amount],
            ['ledger_id' => $credit->id, 'entry_type' => 'Cr', 'amount' => $amount],
        ]);
    }

    private function runReport(User $user, string $path, array $query = [])
    {
        $url = '/api/v1/accounting-reports/' . $path . ($query ? '?' . http_build_query($query) : '');

        return $this->actingAs($user)->getJson($url);
    }

    /**
     * Assert a 422 carries an error for `$key`.
     *
     * ApiResponseWrapper nests every non-2xx body under `data`, so Laravel's
     * own `errors` bag ends up at `data.errors` rather than the top level.
     */
    private function assertValidationErrors($response, string $key): void
    {
        $response->assertStatus(422);

        $errors = $response->json('data.errors') ?? $response->json('errors') ?? [];
        $this->assertArrayHasKey($key, $errors, "Expected a validation error for '{$key}'.");
        $this->assertNotEmpty($errors[$key]);
    }

    /**
     * A small, internally consistent set of books:
     *   opening: Bank 1000 Dr / Capital 1000 Cr (so openings balance)
     *   activity: +500 income, -200 expense
     *
     * @return array<string, LedgerAccount>
     */
    private function balancedBooks(User $admin): array
    {
        $bank = $this->ledger('HDFC Bank', 'Assets', ['is_bank' => true, 'opening_balance' => 1000, 'opening_type' => 'Dr']);
        $capital = $this->ledger('Capital', 'Liabilities', ['opening_balance' => 1000, 'opening_type' => 'Cr']);
        $income = $this->ledger('Commission Income', 'Income');
        $expense = $this->ledger('Office Rent', 'Expenses');

        $this->receipt($admin, $bank, $income, 500, '2026-05-02');
        $this->payment($admin, $expense, $bank, 200, '2026-05-05');

        return compact('bank', 'capital', 'income', 'expense');
    }

    /* --------------------------- ledger statement ------------------------ */

    public function test_ledger_statement_shows_opening_running_balance_and_closing(): void
    {
        $admin = $this->superAdmin();
        $bank = $this->ledger('HDFC Bank', 'Assets', ['is_bank' => true, 'opening_balance' => 1000, 'opening_type' => 'Dr']);
        $party = $this->ledger('Acme Corp', 'Assets', ['is_party_group' => true, 'party_type' => 'customer']);

        $this->receipt($admin, $bank, $party, 500, '2026-05-02');
        $this->payment($admin, $party, $bank, 200, '2026-05-05');

        $response = $this->runReport($admin, "ledger/{$bank->id}", [
            'from' => '2026-05-01', 'to' => '2026-05-31',
        ]);

        $response->assertOk();
        $this->assertSame(1000.0, (float) $response->json('data.opening_balance'));
        $this->assertSame('Dr', $response->json('data.opening_side'));

        $entries = $response->json('data.entries');
        $this->assertCount(2, $entries);
        $this->assertSame(1500.0, (float) $entries[0]['balance']);
        $this->assertSame('Dr', $entries[0]['entry_type']);
        $this->assertSame(1300.0, (float) $entries[1]['balance']);
        $this->assertSame('Cr', $entries[1]['entry_type']);

        $this->assertSame(500.0, (float) $response->json('data.debit_total'));
        $this->assertSame(200.0, (float) $response->json('data.credit_total'));
        $this->assertSame(1300.0, (float) $response->json('data.closing_balance'));
        $this->assertSame('Dr', $response->json('data.closing_side'));
    }

    public function test_ledger_statement_rolls_pre_period_entries_into_the_opening(): void
    {
        $admin = $this->superAdmin();
        $bank = $this->ledger('HDFC Bank', 'Assets', ['is_bank' => true, 'opening_balance' => 1000, 'opening_type' => 'Dr']);
        $party = $this->ledger('Acme Corp', 'Assets', ['is_party_group' => true, 'party_type' => 'customer']);

        $this->receipt($admin, $bank, $party, 500, '2026-05-02');
        $this->payment($admin, $party, $bank, 200, '2026-05-05');

        $response = $this->runReport($admin, "ledger/{$bank->id}", [
            'from' => '2026-05-05', 'to' => '2026-05-31',
        ]);

        $response->assertOk();
        $this->assertSame(1500.0, (float) $response->json('data.opening_balance'));
        $this->assertCount(1, $response->json('data.entries'));
        $this->assertSame(1300.0, (float) $response->json('data.closing_balance'));
    }

    public function test_ledger_statement_of_a_credit_balance_reports_cr_sides(): void
    {
        $admin = $this->superAdmin();
        $supplier = $this->ledger('Vendor Ltd', 'Liabilities', [
            'is_party_group' => true, 'party_type' => 'supplier',
            'opening_balance' => 400, 'opening_type' => 'Cr',
        ]);

        $response = $this->runReport($admin, "ledger/{$supplier->id}", []);

        $response->assertOk();
        $this->assertSame(-400.0, (float) $response->json('data.opening_balance'));
        $this->assertSame('Cr', $response->json('data.opening_side'));
        $this->assertSame(-400.0, (float) $response->json('data.closing_balance'));
        $this->assertSame('Cr', $response->json('data.closing_side'));
    }

    /* ------------------------------- day book ---------------------------- */

    public function test_day_book_lists_only_the_posted_lines_in_the_period(): void
    {
        $admin = $this->superAdmin();
        $bank = $this->ledger('HDFC Bank', 'Assets', ['is_bank' => true]);
        $party = $this->ledger('Acme Corp', 'Assets', ['is_party_group' => true, 'party_type' => 'customer']);

        $this->receipt($admin, $bank, $party, 500, '2026-05-02');
        $this->payment($admin, $party, $bank, 200, '2026-05-05');
        $this->receipt($admin, $bank, $party, 999, '2026-06-15'); // outside the period

        $response = $this->runReport($admin, 'day-book', ['from' => '2026-05-01', 'to' => '2026-05-31']);

        $response->assertOk();
        $this->assertSame(2, $response->json('data.voucher_count'));
        $this->assertCount(4, $response->json('data.rows'));
        $this->assertSame(700.0, (float) $response->json('data.debit_total'));
        $this->assertSame(700.0, (float) $response->json('data.credit_total'));
    }

    public function test_day_book_can_be_filtered_by_voucher_type(): void
    {
        $admin = $this->superAdmin();
        $bank = $this->ledger('HDFC Bank', 'Assets', ['is_bank' => true]);
        $party = $this->ledger('Acme Corp', 'Assets', ['is_party_group' => true, 'party_type' => 'customer']);

        $this->receipt($admin, $bank, $party, 500, '2026-05-02');
        $this->payment($admin, $party, $bank, 200, '2026-05-05');

        $response = $this->runReport($admin, 'day-book', [
            'from' => '2026-05-01', 'to' => '2026-05-31', 'voucher_type' => 'receipt',
        ]);

        $response->assertOk();
        $this->assertSame(1, $response->json('data.voucher_count'));
        $this->assertCount(2, $response->json('data.rows'));
        $this->assertSame(500.0, (float) $response->json('data.debit_total'));
    }

    /* ----------------------------- outstandings -------------------------- */

    public function test_outstanding_receivables_report_bills_and_settlements(): void
    {
        $admin = $this->superAdmin();
        $bank = $this->ledger('HDFC Bank', 'Assets', ['is_bank' => true]);
        $customer = $this->ledger('Acme Corp', 'Assets', ['is_party_group' => true, 'party_type' => 'customer']);
        $sales = $this->ledger('Sales', 'Income');

        $invoice = $this->voucher($admin, 'sales', '2026-05-01', [
            ['ledger_id' => $customer->id, 'entry_type' => 'Dr', 'amount' => 1000],
            ['ledger_id' => $sales->id, 'entry_type' => 'Cr', 'amount' => 1000],
        ], $customer->id);

        // Settle 400 of that bill with a receipt.
        $this->voucher($admin, 'receipt', '2026-05-10', [
            ['ledger_id' => $bank->id, 'entry_type' => 'Dr', 'amount' => 400],
            ['ledger_id' => $customer->id, 'entry_type' => 'Cr', 'amount' => 400],
        ], $customer->id, [
            'bill_allocations' => [
                ['bill_type' => 'sales-voucher', 'bill_id' => $invoice->id, 'amount' => 400],
            ],
        ]);

        $response = $this->runReport($admin, 'outstanding', [
            'side' => 'receivable', 'as_of' => '2026-05-15',
        ]);

        $response->assertOk();
        $data = $response->json('data');

        $this->assertSame(1, $data['bill_count']);
        $this->assertSame(1, $data['party_count']);
        $this->assertSame(600.0, (float) $data['totals']['receivable']);
        $this->assertSame(600.0, (float) $data['grand_total']);

        $bill = $data['bills'][0];
        $this->assertSame('Acme Corp', $bill['party_name']);
        $this->assertSame(1000.0, (float) $bill['total']);
        $this->assertSame(400.0, (float) $bill['settled']);
        $this->assertSame(600.0, (float) $bill['balance']);
        $this->assertSame(14, $bill['days']);
        $this->assertSame('1-30 days', $bill['bucket']);
        $this->assertFalse($bill['overdue']);
        $this->assertSame(600.0, (float) $data['aging']['1-30 days']);
    }

    public function test_outstanding_payables_cover_supplier_bills(): void
    {
        $admin = $this->superAdmin();
        $supplier = $this->ledger('Vendor Ltd', 'Liabilities', ['is_party_group' => true, 'party_type' => 'supplier']);
        $purchase = $this->ledger('Purchases', 'Expenses');

        $this->voucher($admin, 'purchase', '2026-05-03', [
            ['ledger_id' => $purchase->id, 'entry_type' => 'Dr', 'amount' => 800],
            ['ledger_id' => $supplier->id, 'entry_type' => 'Cr', 'amount' => 800],
        ], $supplier->id);

        $payable = $this->runReport($admin, 'outstanding', ['side' => 'payable'])->json('data');
        $this->assertSame(1, $payable['bill_count']);
        $this->assertSame(800.0, (float) $payable['totals']['payable']);

        $receivable = $this->runReport($admin, 'outstanding', ['side' => 'receivable'])->json('data');
        $this->assertSame(0, $receivable['bill_count']);
        $this->assertSame(0.0, (float) $receivable['totals']['receivable']);
    }

    public function test_outstanding_accepts_both_sides_and_reports_aging(): void
    {
        $admin = $this->superAdmin();
        $customer = $this->ledger('Acme Corp', 'Assets', ['is_party_group' => true, 'party_type' => 'customer']);
        $sales = $this->ledger('Sales', 'Income');

        $this->voucher($admin, 'sales', '2026-01-05', [
            ['ledger_id' => $customer->id, 'entry_type' => 'Dr', 'amount' => 100],
            ['ledger_id' => $sales->id, 'entry_type' => 'Cr', 'amount' => 100],
        ], $customer->id);

        $data = $this->runReport($admin, 'outstanding', ['side' => 'both', 'as_of' => '2026-05-15'])
            ->json('data');

        $this->assertSame('both', $data['side']);
        $this->assertSame('Over 90 days', $data['bills'][0]['bucket']);
        $this->assertSame(100.0, (float) $data['aging']['Over 90 days']);
    }

    /* ---------------------------- profit & loss -------------------------- */

    public function test_profit_and_loss_splits_direct_and_indirect_and_finds_net_profit(): void
    {
        $admin = $this->superAdmin();
        $bank = $this->ledger('HDFC Bank', 'Assets', ['is_bank' => true]);
        $sales = $this->ledger('Sales', 'Income', ['affects_gross_profit' => true]);
        $direct = $this->ledger('Direct Costs', 'Expenses', ['affects_gross_profit' => true]);
        $otherIncome = $this->ledger('Misc Income', 'Income');
        $indirect = $this->ledger('Salaries', 'Expenses');

        $this->receipt($admin, $bank, $sales, 1000, '2026-05-02');   // income 1000
        $this->payment($admin, $direct, $bank, 300, '2026-05-03');   // direct expense 300
        $this->voucher($admin, 'journal', '2026-05-04', [
            ['ledger_id' => $indirect->id, 'entry_type' => 'Dr', 'amount' => 100],
            ['ledger_id' => $otherIncome->id, 'entry_type' => 'Cr', 'amount' => 100],
        ]);

        $response = $this->runReport($admin, 'profit-loss', ['from' => '2026-04-01', 'to' => '2026-05-31']);

        $response->assertOk();
        $data = $response->json('data');

        $this->assertSame(1000.0, (float) $data['direct_income']);
        $this->assertSame(300.0, (float) $data['direct_expenses']);
        $this->assertSame(700.0, (float) $data['gross_profit']);
        $this->assertSame(100.0, (float) $data['indirect_income']);
        $this->assertSame(100.0, (float) $data['indirect_expenses']);
        $this->assertSame(1100.0, (float) $data['total_income']);
        $this->assertSame(400.0, (float) $data['total_expenses']);
        $this->assertSame(700.0, (float) $data['net_profit']);
        $this->assertFalse($data['is_loss']);

        // The bank is an asset, so it must never appear in the P&L.
        $incomeNames = array_column(array_merge($data['income'], []), 'group');
        $this->assertNotContains('HDFC Bank Group', $incomeNames);
        $this->assertNotContains('HDFC Bank Group', array_column($data['expenses'], 'group'));
    }

    public function test_profit_and_loss_defaults_to_the_active_financial_year(): void
    {
        $admin = $this->superAdmin();
        $bank = $this->ledger('HDFC Bank', 'Assets', ['is_bank' => true]);
        $sales = $this->ledger('Sales', 'Income');
        $this->receipt($admin, $bank, $sales, 500, '2026-05-02');

        $data = $this->runReport($admin, 'profit-loss')->json('data');

        $this->assertSame('2026-04-01', $data['period']['from']);
        $this->assertSame(500.0, (float) $data['total_income']);
        $this->assertSame(500.0, (float) $data['net_profit']);
    }

    /* ----------------------------- balance sheet ------------------------- */

    public function test_balance_sheet_balances_with_the_profit_carried_in(): void
    {
        $admin = $this->superAdmin();
        $this->balancedBooks($admin);

        $response = $this->runReport($admin, 'balance-sheet', ['to' => '2026-05-31']);

        $response->assertOk();
        $data = $response->json('data');

        $this->assertSame(1300.0, (float) $data['assets_total']);
        $this->assertSame(1000.0, (float) $data['liabilities_total']);
        $this->assertSame(300.0, (float) $data['profit']);
        $this->assertSame('Profit for the period', $data['profit_line']);
        $this->assertSame(1300.0, (float) $data['liabilities_grand_total']);
        $this->assertSame(0.0, (float) $data['difference']);
        $this->assertTrue($data['is_balanced']);
    }

    public function test_trial_balance_ties_for_balanced_books(): void
    {
        $admin = $this->superAdmin();
        $this->balancedBooks($admin);

        $response = $this->runReport($admin, 'trial-balance', ['to' => '2026-05-31']);

        $response->assertOk();
        $data = $response->json('data');

        $this->assertSame(4, $data['account_count']);
        $this->assertSame(1500.0, (float) $data['total_debit']);
        $this->assertSame(1500.0, (float) $data['total_credit']);
        $this->assertSame(0.0, (float) $data['difference']);
        $this->assertTrue($data['is_balanced']);

        // Each account lands on its natural side exactly once. Compare with a
        // tolerance — JSON drops the `.0` from a zero float, so a strict
        // `=== 0.0` would compare int(0) against float(0.0).
        foreach ($data['rows'] as $row) {
            $debit = (float) $row['debit'];
            $credit = (float) $row['credit'];

            $this->assertTrue(
                ($debit > 0 && $credit < 0.005) || ($credit > 0 && $debit < 0.005),
                "Row {$row['name']} must be on one side only (Dr {$debit} / Cr {$credit})."
            );
        }
    }

    public function test_trial_balance_exposes_books_that_do_not_add_up(): void
    {
        $admin = $this->superAdmin();
        // Opening balances that don't tie: assets exceed capital with nothing
        // on the liability side to make up the difference.
        $this->ledger('HDFC Bank', 'Assets', ['is_bank' => true, 'opening_balance' => 1000, 'opening_type' => 'Dr']);
        $this->ledger('Capital', 'Liabilities', ['opening_balance' => 500, 'opening_type' => 'Cr']);

        $data = $this->runReport($admin, 'trial-balance', ['to' => '2026-05-31'])->json('data');

        $this->assertSame(500.0, (float) $data['difference']);
        $this->assertFalse($data['is_balanced']);

        // The same imbalance must surface as an unbalanced Balance Sheet.
        $sheet = $this->runReport($admin, 'balance-sheet', ['to' => '2026-05-31'])->json('data');
        $this->assertFalse($sheet['is_balanced']);
        $this->assertSame(500.0, (float) $sheet['difference']);
    }

    /* --------------------------- cash / bank book ------------------------ */

    public function test_cash_bank_book_covers_bank_and_cash_accounts(): void
    {
        $admin = $this->superAdmin();
        $bank = $this->ledger('HDFC Bank', 'Assets', ['is_bank' => true, 'opening_balance' => 1000, 'opening_type' => 'Dr']);
        $cash = $this->ledger('Petty Cash', 'Assets', ['is_cash' => true, 'opening_balance' => 100, 'opening_type' => 'Dr']);
        $income = $this->ledger('Commission Income', 'Income');
        $expense = $this->ledger('Office Rent', 'Expenses');

        $this->receipt($admin, $cash, $income, 50, '2026-05-02');
        $this->payment($admin, $expense, $bank, 200, '2026-05-05');

        $response = $this->runReport($admin, 'cash-bank-book', [
            'from' => '2026-04-01', 'to' => '2026-05-31',
        ]);

        $response->assertOk();
        $data = $response->json('data');

        $this->assertCount(2, $data['accounts']);

        $bankRow = collect($data['accounts'])->firstWhere('ledger_id', $bank->id);
        $this->assertSame(1000.0, (float) $bankRow['opening']);
        $this->assertSame(0.0, (float) $bankRow['debit']);
        $this->assertSame(200.0, (float) $bankRow['credit']);
        $this->assertSame(800.0, (float) $bankRow['closing']);

        $cashRow = collect($data['accounts'])->firstWhere('ledger_id', $cash->id);
        $this->assertSame(100.0, (float) $cashRow['opening']);
        $this->assertSame(50.0, (float) $cashRow['debit']);
        $this->assertSame(150.0, (float) $cashRow['closing']);

        $this->assertSame(950.0, (float) $data['totals']['closing']);

        // Requesting one account brings its transaction lines too.
        $focused = $this->runReport($admin, 'cash-bank-book', [
            'from' => '2026-04-01', 'to' => '2026-05-31', 'ledger_id' => $bank->id,
        ])->json('data');

        $this->assertSame('HDFC Bank', $focused['ledger']['name']);
        $this->assertCount(1, $focused['entries']);
        $this->assertSame(200.0, (float) $focused['entries'][0]['amount']);
    }

    /* ----------------------------- permissions --------------------------- */

    public function test_reports_require_the_finance_reports_permission(): void
    {
        $admin = $this->superAdmin();
        $books = $this->balancedBooks($admin);

        $granted = $this->userWithPermissions(['finance-reports.view']);
        $this->runReport($granted, 'profit-loss')->assertOk();
        $this->runReport($granted, "ledger/{$books['bank']->id}")->assertOk();

        $stranger = User::factory()->create();
        $this->runReport($stranger, 'profit-loss')->assertStatus(403);
        $this->runReport($stranger, 'trial-balance')->assertStatus(403);
        $this->runReport($stranger, 'outstanding')->assertStatus(403);
        $this->runReport($stranger, "ledger/{$books['bank']->id}")->assertStatus(403);
    }

    /* ------------------------------ validation --------------------------- */

    public function test_an_invalid_period_is_rejected(): void
    {
        $admin = $this->superAdmin();

        // A well-formed but backwards period is caught by the service...
        $this->assertValidationErrors(
            $this->runReport($admin, 'day-book', ['from' => '2026-06-01', 'to' => '2026-05-01']),
            'from'
        );

        // ...and a malformed date never gets that far.
        $this->assertValidationErrors(
            $this->runReport($admin, 'day-book', ['from' => 'not-a-date']),
            'from'
        );
    }

    public function test_an_unknown_outstanding_side_is_rejected(): void
    {
        $admin = $this->superAdmin();

        $this->assertValidationErrors(
            $this->runReport($admin, 'outstanding', ['side' => 'sideways']),
            'side'
        );
    }
}
