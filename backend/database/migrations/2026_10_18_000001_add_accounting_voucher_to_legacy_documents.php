<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Points a legacy document at the voucher it was migrated into.
     *
     * `accounting:migrate-legacy` re-keys invoices as sales vouchers and
     * purchase orders as purchase vouchers. Once a document carries a voucher
     * id it stops being a bill in its own right: the Outstandings report drops
     * it and reads the voucher instead, so one document can never be counted
     * in both worlds.
     *
     * A plain column rather than a foreign key, matching the soft `bill_id`
     * reference on voucher_bill_allocations. MySQL would take a constraint, but
     * SQLite (the test suite) cannot add one to an existing table, and a stale
     * id here only means "this document was already migrated".
     */
    public function up(): void
    {
        $this->addColumn('invoices');
        $this->addColumn('purchase_orders');
    }

    public function down(): void
    {
        $this->dropColumn('invoices');
        $this->dropColumn('purchase_orders');
    }

    private function addColumn(string $table): void
    {
        if (! Schema::hasTable($table) || Schema::hasColumn($table, 'accounting_voucher_id')) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) {
            $blueprint->unsignedBigInteger('accounting_voucher_id')->nullable()->index();
        });
    }

    private function dropColumn(string $table): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'accounting_voucher_id')) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) {
            $blueprint->dropColumn('accounting_voucher_id');
        });
    }
};
