<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Convert existing suppliers and customers into ledger_accounts so the
     * "Party ledger *" dropdown in purchase/sales voucher forms is populated.
     *
     * - Suppliers  → ledger under "Sundry Creditors" group  (party_type = 'supplier',  opening Cr)
     *   Customers  → ledger under "Sundry Debtors"   group  (party_type = 'customer', opening Dr)
     *
     * Each new ledger keeps party_id pointing back to the original supplier/customer
     * row and copies across gst_number. Opening balance defaults to 0 (you can
     * edit it afterwards on the ledger form).
     *
     * The suppliers / customers tables are kept intact because purchase_orders
     * and sales_orders still FK-reference them.
     */
    public function up(): void
    {
        $supplierCount = 0;
        $customerCount = 0;
        $skipped = [];

        // ------------------------------------------------------------------
        // 1. Ensure party groups exist
        // ------------------------------------------------------------------
        $creditorGroup = DB::table('account_groups')
            ->where('is_party_group', true)
            ->where('party_type', 'supplier')
            ->first();

        if (!$creditorGroup) {
            $creditorGroupId = DB::table('account_groups')->insertGetId([
                'name' => 'Sundry Creditors',
                'nature' => 'Liabilities',
                'is_primary' => false,
                'affects_gross_profit' => false,
                'is_party_group' => true,
                'party_type' => 'supplier',
                'is_system' => true,
                'sort_order' => 100,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $creditorGroup = (object) ['id' => $creditorGroupId];
        }

        $debtorGroup = DB::table('account_groups')
            ->where('is_party_group', true)
            ->where('party_type', 'customer')
            ->first();

        if (!$debtorGroup) {
            $debtorGroupId = DB::table('account_groups')->insertGetId([
                'name' => 'Sundry Debtors',
                'nature' => 'Assets',
                'is_primary' => false,
                'affects_gross_profit' => false,
                'is_party_group' => true,
                'party_type' => 'customer',
                'is_system' => true,
                'sort_order' => 101,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $debtorGroup = (object) ['id' => $debtorGroupId];
        }

        $now = now();

        // ------------------------------------------------------------------
        // 2. Convert suppliers → ledger accounts under creditors group
        // ------------------------------------------------------------------
        $suppliers = DB::table('suppliers')->get();

        foreach ($suppliers as $s) {
            $ledgerName = $this->uniqueLedgerName($s->name);

            try {
                DB::table('ledger_accounts')->insert([
                    'name' => $ledgerName,
                    'group_id' => $creditorGroup->id,
                    'opening_balance' => 0,
                    'opening_type' => 'Cr',
                    'is_bank' => false,
                    'is_cash' => false,
                    'gst_number' => $s->gst_number,
                    'party_type' => 'supplier',
                    'party_id' => $s->id,
                    'is_system' => false,
                    'status' => 'active',
                    'notes' => 'Converted from suppliers table.',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $supplierCount++;
            } catch (\Throwable $e) {
                $skipped[] = "Supplier id={$s->id} name={$s->name}: ".$e->getMessage();
            }
        }

        // ------------------------------------------------------------------
        // 3. Convert customers → ledger accounts under debtors group
        // ------------------------------------------------------------------
        $customers = DB::table('customers')->get();

        foreach ($customers as $c) {
            $ledgerName = $this->uniqueLedgerName($c->name);

            try {
                DB::table('ledger_accounts')->insert([
                    'name' => $ledgerName,
                    'group_id' => $debtorGroup->id,
                    'opening_balance' => 0,
                    'opening_type' => 'Dr',
                    'is_bank' => false,
                    'is_cash' => false,
                    'gst_number' => $c->gst_number,
                    'party_type' => 'customer',
                    'party_id' => $c->id,
                    'is_system' => false,
                    'status' => 'active',
                    'notes' => 'Converted from customers table.',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $customerCount++;
            } catch (\Throwable $e) {
                $skipped[] = "Customer id={$c->id} name={$c->name}: ".$e->getMessage();
            }
        }

        // ------------------------------------------------------------------
        // 4. Report
        // ------------------------------------------------------------------
        $report = [
            'creditor_group_id' => $creditorGroup->id,
            'debtor_group_id' => $debtorGroup->id,
            'suppliers_converted' => $supplierCount,
            'customers_converted' => $customerCount,
            'skipped' => $skipped,
        ];

        \Log::info('supplier_customer_to_ledger_conversion', $report);

        echo "\n=== Supplier/Customer → Ledger conversion complete ===\n";
        echo "Creditor group id: {$creditorGroup->id}\n";
        echo "Debtor group id:   {$debtorGroup->id}\n";
        echo "Suppliers converted: {$supplierCount}\n";
        echo "Customers converted: {$customerCount}\n";
        if ($skipped) {
            echo "Skipped:\n";
            foreach ($skipped as $s) {
                echo "  - {$s}\n";
            }
        }
        echo "Opening balances default to 0. Edit them on the ledger form if needed.\n\n";
    }

    public function down(): void
    {
        // Remove the ledger accounts we created (those linked to suppliers/customers)
        DB::table('ledger_accounts')
            ->where('notes', 'like', '%Converted from suppliers table%')
            ->orWhere('notes', 'like', '%Converted from customers table%')
            ->delete();

        // Remove the system party groups we created (only if empty)
        DB::table('account_groups')
            ->where('is_system', true)
            ->whereIn('party_type', ['supplier', 'customer'])
            ->where('name', 'like', 'Sundry %')
            ->delete();
    }

    /**
     * Return a unique ledger account name. ledger_accounts.name is unique.
     */
    protected function uniqueLedgerName(string $desired): string
    {
        $name = $desired;
        $suffix = 1;

        while (DB::table('ledger_accounts')->where('name', $name)->exists()) {
            $name = $desired.' ('.$suffix.')';
            $suffix++;
        }

        return $name;
    }
};
