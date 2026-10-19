<?php

namespace Tests\Feature;

use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Models\FinancialYear;
use App\Models\LedgerAccount;
use App\Models\Supplier;
use App\Services\Accounting\PartyLedgerService;
use Database\Seeders\ChartOfAccountsSeeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

/**
 * Slice 6: the bridge between the party masters and the books.
 *
 * Every customer gets a Sundry Debtors ledger and every supplier a Sundry
 * Creditors one, linked by party_type + party_id. Nothing else in the
 * accounting module can see a legacy invoice or purchase order without it.
 *
 * Only the migrations these paths touch are run — the full chain is
 * MySQL-specific in places, as with the other accounting suites.
 */
class PartyLedgerLinkTest extends TestCase
{
    private const MIGRATIONS = [
        '2026_06_06_085506_create_suppliers_table.php',
        '2026_06_06_085507_create_customers_table.php',
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

        FinancialYear::create([
            'name' => '2026-2027',
            'start_date' => '2026-04-01',
            'end_date' => '2027-03-31',
            'is_active' => true,
        ]);
    }

    /* ------------------------------ fixtures ----------------------------- */

    private function seedChartOfAccounts(): void
    {
        (new ChartOfAccountsSeeder)->run();
    }

    /**
     * A master written straight to the table, so no model event fires — exactly
     * how every existing customer and supplier looks before this slice.
     */
    private function legacyMaster(string $table, string $name): int
    {
        return DB::table($table)->insertGetId([
            'name' => $name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /* ------------------------------ backfill ----------------------------- */

    public function test_backfill_links_every_master_into_the_right_party_group(): void
    {
        $this->seedChartOfAccounts();

        // Deliberately the same name on both sides: ledger names are globally
        // unique, so one of them has to be qualified.
        $customerId = $this->legacyMaster('customers', 'Acme Corp');
        $supplierId = $this->legacyMaster('suppliers', 'Acme Corp');

        $summary = app(PartyLedgerService::class)->linkAll();

        $this->assertSame(['created' => 1, 'existing' => 0], $summary['customer']);
        $this->assertSame(['created' => 1, 'existing' => 0], $summary['supplier']);
        $this->assertSame([], $summary['errors']);

        $customerLedger = LedgerAccount::where('party_type', 'customer')->where('party_id', $customerId)->first();
        $supplierLedger = LedgerAccount::where('party_type', 'supplier')->where('party_id', $supplierId)->first();

        $this->assertSame('Acme Corp', $customerLedger->name);
        $this->assertSame('Acme Corp (Supplier)', $supplierLedger->name);

        $this->assertSame('Sundry Debtors', $customerLedger->group->name);
        $this->assertSame('Assets', $customerLedger->group->nature);
        $this->assertSame('Sundry Creditors', $supplierLedger->group->name);
        $this->assertSame('Liabilities', $supplierLedger->group->nature);
    }

    public function test_backfill_is_idempotent(): void
    {
        $this->seedChartOfAccounts();
        $this->legacyMaster('customers', 'Acme Corp');

        $first = app(PartyLedgerService::class)->linkAll();
        $second = app(PartyLedgerService::class)->linkAll();

        $this->assertSame(1, $first['customer']['created']);
        $this->assertSame(0, $first['customer']['existing']);

        $this->assertSame(0, $second['customer']['created']);
        $this->assertSame(1, $second['customer']['existing']);

        $this->assertSame(1, LedgerAccount::where('party_type', 'customer')->count());
    }

    public function test_backfill_points_at_the_seeder_when_party_groups_are_missing(): void
    {
        $this->legacyMaster('customers', 'Acme Corp');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('ChartOfAccountsSeeder');

        app(PartyLedgerService::class)->linkAll();
    }

    /* -------------------------- creating a master ------------------------ */

    public function test_a_master_created_today_gets_its_ledger_immediately(): void
    {
        $this->seedChartOfAccounts();

        $customer = Customer::create(['name' => 'Beta Traders']);
        $supplier = Supplier::create(['name' => 'Gamma Mills']);

        $this->assertSame($customer->id, $customer->fresh()->ledger->party_id);
        $this->assertSame('Beta Traders', $customer->fresh()->ledger->name);
        $this->assertSame('Gamma Mills', $supplier->fresh()->ledger->name);
        $this->assertSame('Sundry Creditors', $supplier->fresh()->ledger->group->name);
    }

    public function test_creating_a_master_survives_a_missing_chart_of_accounts(): void
    {
        // The observer fails soft rather than turning a sales desk's new
        // customer into a 500 because the accountant has not seeded yet.
        $customer = Customer::create(['name' => 'No Books Yet']);

        $this->assertNotNull($customer->id);
        $this->assertNull($customer->fresh()->ledger);
    }

    public function test_a_party_named_like_a_system_ledger_is_qualified(): void
    {
        $this->seedChartOfAccounts();

        $cash = LedgerAccount::findByName('Cash');
        $this->assertNotNull($cash);

        $ledger = Customer::create(['name' => 'Cash'])->fresh()->ledger;

        $this->assertSame('Cash (Customer)', $ledger->name);
        $this->assertNotSame($cash->id, $ledger->id);
    }

    /* --------------------------- relation safety ------------------------- */

    public function test_customer_and_supplier_ledgers_never_cross_when_the_ids_match(): void
    {
        $this->seedChartOfAccounts();

        // Separate tables, so the first of each gets the same id — the case a
        // bare hasOne('party_id') would get wrong.
        $customerId = $this->legacyMaster('customers', 'Twin');
        $supplierId = $this->legacyMaster('suppliers', 'Twin');
        $this->assertSame($customerId, $supplierId);

        app(PartyLedgerService::class)->linkAll();

        $customerLedger = Customer::find($customerId)->ledger;
        $supplierLedger = Supplier::find($supplierId)->ledger;

        $this->assertSame('customer', $customerLedger->party_type);
        $this->assertSame('supplier', $supplierLedger->party_type);
        $this->assertNotSame($customerLedger->id, $supplierLedger->id);
    }

    /* ------------------------------- command ----------------------------- */

    public function test_command_reports_a_dry_run_then_creates(): void
    {
        $this->seedChartOfAccounts();
        $this->legacyMaster('customers', 'Acme Corp');

        $this->artisan('accounting:link-parties', ['--dry-run' => true])->assertExitCode(0);
        $this->assertSame(0, LedgerAccount::where('party_type', 'customer')->count());

        $this->artisan('accounting:link-parties')->assertExitCode(0);
        $this->assertSame(1, LedgerAccount::where('party_type', 'customer')->count());
    }

    /* -------------------------------- API -------------------------------- */

    /**
     * The controller loads `ledger.group` — assert that exact string resolves,
     * so a typo there cannot silently make every profile say "Not linked".
     */
    public function test_the_controller_eager_load_resolves_the_group(): void
    {
        $this->seedChartOfAccounts();

        $customer = Customer::create(['name' => 'Beta Traders']);

        $loaded = Customer::with('ledger.group')->findOrFail($customer->id);

        $this->assertTrue($loaded->relationLoaded('ledger'));
        $this->assertSame($customer->ledger->id, $loaded->ledger->id);
        $this->assertSame('Sundry Debtors', $loaded->ledger->group->name);
    }

    public function test_resource_exposes_the_linked_ledger(): void
    {
        $this->seedChartOfAccounts();

        $customer = Customer::create(['name' => 'Beta Traders']);

        $data = (new CustomerResource($customer->load('ledger.group')))->resolve(request());

        $this->assertSame($customer->ledger->id, $data['ledger']['id']);
        $this->assertSame('Beta Traders', $data['ledger']['name']);
        $this->assertSame('Sundry Debtors', $data['ledger']['group']);
        $this->assertSame('active', $data['ledger']['status']);
    }

    public function test_resource_omits_the_ledger_key_before_one_exists(): void
    {
        $customer = Customer::create(['name' => 'No Books Yet']);

        $data = (new CustomerResource($customer->load('ledger.group')))->resolve(request());

        $this->assertArrayNotHasKey('ledger', $data);
    }
}
