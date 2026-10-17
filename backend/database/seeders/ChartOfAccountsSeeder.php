<?php

namespace Database\Seeders;

use App\Models\AccountGroup;
use App\Models\FinancialYear;
use App\Models\LedgerAccount;
use Illuminate\Database\Seeder;

/**
 * Seeds the Tally default chart of accounts and a starting financial year.
 *
 * Safe to re-run: every group/ledger is matched by name and only created when
 * missing, so existing edits are never overwritten.
 */
class ChartOfAccountsSeeder extends Seeder
{
    /**
     * Primary groups and their nature. is_party_group marks Sundry Debtors and
     * Sundry Creditors; affects_gross_profit marks the direct groups.
     */
    protected array $groups = [
        // name, nature, affects_gross_profit, is_party_group, party_type
        ['Capital Account', 'Liabilities', false, false, null],
        ['Loans (Liability)', 'Liabilities', false, false, null],
        ['Current Liabilities', 'Liabilities', false, false, null],
        ['Current Assets', 'Assets', false, false, null],
        ['Fixed Assets', 'Assets', false, false, null],
        ['Investments', 'Assets', false, false, null],
        ['Sundry Debtors', 'Assets', false, true, 'customer'],
        ['Sundry Creditors', 'Liabilities', false, true, 'supplier'],
        ['Bank Accounts', 'Assets', false, false, null],
        ['Cash-in-Hand', 'Assets', false, false, null],
        ['Sales Accounts', 'Income', true, false, null],
        ['Purchase Accounts', 'Expenses', true, false, null],
        ['Direct Incomes', 'Income', true, false, null],
        ['Direct Expenses', 'Expenses', true, false, null],
        ['Indirect Incomes', 'Income', false, false, null],
        ['Indirect Expenses', 'Expenses', false, false, null],
        ['Duties & Taxes', 'Liabilities', false, false, null],
        ['Provisions', 'Liabilities', false, false, null],
        ['Reserves & Surplus', 'Liabilities', false, false, null],
        ['Secured Loans', 'Liabilities', false, false, null],
        ['Unsecured Loans', 'Liabilities', false, false, null],
        ['Bank OD A/c', 'Liabilities', false, false, null],
        ['Deposits (Asset)', 'Assets', false, false, null],
        ['Loans & Advances (Asset)', 'Assets', false, false, null],
        ['Stock-in-Hand', 'Assets', false, false, null],
        ['Suspense Account', 'Liabilities', false, false, null],
        ['Branch / Divisions', 'Liabilities', false, false, null],
        ['Misc. Expenses (ASSET)', 'Assets', false, false, null],
    ];

    /** Default ledgers every company needs: name, group name. */
    protected array $ledgers = [
        ['Cash', 'Cash-in-Hand'],
        ['Sales', 'Sales Accounts'],
        ['Purchase', 'Purchase Accounts'],
        ['CGST', 'Duties & Taxes'],
        ['SGST', 'Duties & Taxes'],
        ['IGST', 'Duties & Taxes'],
        ['Round Off', 'Indirect Expenses'],
        ['Discount Allowed', 'Indirect Expenses'],
        ['Discount Received', 'Indirect Incomes'],
        ['Salary', 'Indirect Expenses'],
        ['Rent', 'Indirect Expenses'],
        ['Bank Charges', 'Indirect Expenses'],
        ['Interest Received', 'Indirect Incomes'],
    ];

    public function run(): void
    {
        $order = 0;

        foreach ($this->groups as [$name, $nature, $direct, $isParty, $partyType]) {
            AccountGroup::updateOrCreate(
                ['name' => $name],
                [
                    'nature' => $nature,
                    'is_primary' => true,
                    'affects_gross_profit' => $direct,
                    'is_party_group' => $isParty,
                    'party_type' => $partyType,
                    'is_system' => true,
                    'sort_order' => $order++,
                ]
            );
        }

        foreach ($this->ledgers as [$name, $groupName]) {
            $group = AccountGroup::where('name', $groupName)->first();
            if (! $group) {
                continue;
            }

            LedgerAccount::updateOrCreate(
                ['name' => $name],
                [
                    'group_id' => $group->id,
                    'is_cash' => $name === 'Cash',
                    'is_system' => true,
                ]
            );
        }

        $this->seedFinancialYear();
    }

    /** Ensure a current financial year exists and is active. */
    protected function seedFinancialYear(): void
    {
        if (FinancialYear::exists()) {
            return;
        }

        // Indian financial year runs 1 April - 31 March.
        $now = now();
        $startYear = $now->month >= 4 ? $now->year : $now->year - 1;

        FinancialYear::create([
            'name' => "{$startYear}-" . ($startYear + 1),
            'start_date' => "{$startYear}-04-01",
            'end_date' => ($startYear + 1) . '-03-31',
            'is_active' => true,
            'is_closed' => false,
        ]);
    }
}
