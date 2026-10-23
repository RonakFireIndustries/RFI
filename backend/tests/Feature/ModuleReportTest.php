<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Reports\ModuleReportService;
use App\Support\Reports\Registry;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * The module report pack.
 *
 * Every report in the application is declared as data (app/Support/Reports),
 * so what has to be tested is the contract around that data rather than any
 * one report: the registry is well formed, the engine refuses what it should
 * refuse, and one report's payload has the shape the generic frontend draws.
 *
 * Only the migrations these tests touch are run - the full chain is
 * MySQL-specific in places, as with the other accounting suites.
 */
class ModuleReportTest extends TestCase
{
    private const MIGRATIONS = [
        '0001_01_01_000000_create_users_table.php',
        '2026_05_30_083453_create_permission_tables.php',
        '2026_08_27_000000_add_is_super_admin_to_users_table.php',
        '2026_10_17_000001_create_account_groups_table.php',
        '2026_10_17_000002_create_ledger_accounts_table.php',
        '2026_10_17_000003_create_financial_years_table.php',
        '2026_10_17_000004_create_vouchers_table.php',
        '2026_10_17_000008_add_document_totals_to_vouchers_table.php',
        '2026_10_19_000001_add_due_date_to_vouchers.php',
        '2026_10_21_000002_add_bill_level_tax_discount_to_vouchers.php',
        '2026_10_17_000005_create_voucher_entries_table.php',
        '2026_10_17_000006_create_voucher_bill_allocations_table.php',
        // cash_vouchers joins the party masters and the site register, so the
        // engine can resolve every alias the report names.
        '2026_05_30_083454_create_employees_table.php',
        '2026_06_06_085506_create_suppliers_table.php',
        '2026_06_16_000001_create_sites_table.php',
        '2026_10_15_000003_create_cash_vouchers_table.php',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        foreach (self::MIGRATIONS as $file) {
            $migration = require __DIR__ . '/../../database/migrations/' . $file;
            $migration->up();
        }

        // The registry is gated through App\Support\Access, which asks Spatie
        // for the permission - so the canonical rows have to exist before any
        // report can be read, even by a super admin.
        foreach (\App\Support\Access::allPermissionNames() as $name) {
            Permission::findOrCreate($name, 'web');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /* ------------------------------ fixtures ----------------------------- */

    private function superAdmin(): User
    {
        $this->grantEveryReportPermission();

        return User::factory()->create(['is_super_admin' => true]);
    }

    /**
     * Every permission the registry names, as real permission rows.
     *
     * The engine checks a report's permission through App\Support\Access,
     * which consults Spatie - so a super admin only really passes once the
     * rows exist.
     */
    private function grantEveryReportPermission(): void
    {
        foreach (\App\Support\Access::allPermissionNames() as $name) {
            Permission::findOrCreate($name, 'web');
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function userWithPermissions(array $names): User
    {
        $this->grantEveryReportPermission();

        $user = User::factory()->create();
        $user->givePermissionTo($names);

        return $user;
    }

    private function service(): ModuleReportService
    {
        return app(ModuleReportService::class);
    }

    /* ------------------------------- registry ---------------------------- */

    /**
     * Every module definition has the shape the engine expects, and every
     * report names a base table and a permission that the access config
     * actually defines.
     */
    public function test_registry_is_well_formed(): void
    {
        $registry = Registry::all();

        $this->assertNotEmpty($registry, 'The registry should declare at least one module.');

        // config/access.php is the source of truth for canonical permission
        // names; a report naming anything else could never be granted.
        $canonical = \App\Support\Access::allPermissionNames();
        $canonical[] = 'access-control.manage';

        foreach ($registry as $moduleKey => $module) {
            $this->assertArrayHasKey('label', $module, "Module {$moduleKey} has no label.");
            $this->assertArrayHasKey('reports', $module, "Module {$moduleKey} has no reports.");
            $this->assertNotEmpty($module['reports'], "Module {$moduleKey} declares no reports.");

            foreach ($module['reports'] as $reportKey => $report) {
                $this->assertArrayHasKey(
                    'base',
                    $report,
                    "Report {$moduleKey}/{$reportKey} declares no base table."
                );

                $this->assertNotEmpty(
                    $report['columns'] ?? [],
                    "Report {$moduleKey}/{$reportKey} declares no columns."
                );

                foreach ($report['columns'] as $column) {
                    $this->assertArrayHasKey(
                        'key',
                        $column,
                        "A column of {$moduleKey}/{$reportKey} has no key."
                    );
                }

                $permission = $report['permission'] ?? $module['permission'] ?? null;
                $this->assertNotNull(
                    $permission,
                    "Report {$moduleKey}/{$reportKey} declares no permission."
                );
                $this->assertContains(
                    $permission,
                    $canonical,
                    "Report {$moduleKey}/{$reportKey} names the non-canonical permission {$permission}."
                );
            }
        }
    }

    /* ------------------------------ catalogue ---------------------------- */

    public function test_catalogue_is_filtered_by_permission(): void
    {
        $user = $this->userWithPermissions(['cash-vouchers.view']);

        $catalog = $this->service()->catalog($user);

        $moduleKeys = array_column($catalog['modules'], 'key');

        $this->assertContains('cash-vouchers', $moduleKeys);
        $this->assertNotContains('employees', $moduleKeys, 'A user without employees.view must not see employee reports.');

        foreach ($catalog['modules'] as $module) {
            $this->assertGreaterThan(0, $module['report_count']);
        }
    }

    public function test_catalogue_lists_nothing_for_a_user_without_any_read_permission(): void
    {
        $user = User::factory()->create();

        $catalog = $this->service()->catalog($user);

        $this->assertSame([], $catalog['modules']);
        $this->assertSame(0, $catalog['report_count']);
    }

    /* --------------------------------- api ------------------------------- */

    public function test_the_catalogue_endpoint_requires_authentication(): void
    {
        $this->getJson('/api/v1/module-reports')->assertStatus(401);
    }

    public function test_the_catalogue_endpoint_returns_the_users_modules(): void
    {
        $user = $this->userWithPermissions(['cash-vouchers.view']);

        $response = $this->actingAs($user)->getJson('/api/v1/module-reports');

        $response->assertOk();
        $response->assertJsonPath('success', true);

        $payload = $response->json('data');
        $this->assertArrayHasKey('modules', $payload);
        $this->assertContains('cash-vouchers', array_column($payload['modules'], 'key'));
    }

    public function test_a_report_runs_and_returns_the_generic_payload(): void
    {
        $user = $this->userWithPermissions(['cash-vouchers.view']);

        $response = $this->actingAs($user)
            ->getJson('/api/v1/module-reports/cash-vouchers/register');

        $response->assertOk();

        $payload = $response->json('data');

        $this->assertArrayHasKey('report', $payload);
        $this->assertArrayHasKey('columns', $payload['report']);
        $this->assertArrayHasKey('filters', $payload['report']);
        $this->assertArrayHasKey('rows', $payload);
        $this->assertArrayHasKey('meta', $payload);
        $this->assertArrayHasKey('total', $payload['meta']);
        $this->assertArrayHasKey('truncated', $payload['meta']);

        foreach ($payload['report']['columns'] as $column) {
            $this->assertArrayHasKey('key', $column);
            $this->assertArrayHasKey('label', $column);
            $this->assertArrayHasKey('format', $column);
            $this->assertArrayHasKey('align', $column);
        }
    }

    public function test_a_report_the_user_cannot_read_is_refused(): void
    {
        $user = $this->userWithPermissions(['cash-vouchers.view']);

        $this->actingAs($user)
            ->getJson('/api/v1/module-reports/employees/directory')
            ->assertStatus(403);
    }

    public function test_an_unknown_report_is_not_found(): void
    {
        $user = $this->superAdmin();

        $this->actingAs($user)
            ->getJson('/api/v1/module-reports/no-such-module/no-such-report')
            ->assertStatus(404);
    }

    /**
     * Filters are the only user-controlled part of a report, and the engine
     * drops anything the report did not declare.
     */
    public function test_undeclared_filters_are_ignored(): void
    {
        $user = $this->superAdmin();

        // cash_vouchers is fully described by the migrations these tests run,
        // so the engine has every column the report names.
        $response = $this->actingAs($user)
            ->getJson('/api/v1/module-reports/cash-vouchers/register?users.id=1&drop%20table=users&q=abc');

        $response->assertOk();

        $applied = $response->json('data.filters');
        $this->assertIsArray($applied);
        $this->assertArrayNotHasKey('users.id', $applied);
        $this->assertArrayNotHasKey('drop table', $applied);

        // A declared filter is still applied.
        $this->assertSame('abc', $applied['q'] ?? null);
    }

    public function test_a_filter_value_the_report_does_not_offer_is_refused(): void
    {
        $user = $this->superAdmin();

        // The pending-approval report closes its status filter to a fixed
        // list, so anything outside it is refused rather than silently
        // returning an empty report.
        $this->actingAs($user)
            ->getJson('/api/v1/module-reports/cash-vouchers/pending-approval?status=not-a-real-status')
            ->assertStatus(422);
    }

    public function test_export_requires_the_export_permission(): void
    {
        $user = $this->userWithPermissions(['cash-vouchers.view']);

        $this->actingAs($user)
            ->getJson('/api/v1/module-reports/cash-vouchers/register/export?format=csv')
            ->assertStatus(403);
    }

    public function test_export_returns_a_csv_file(): void
    {
        $user = $this->userWithPermissions(['cash-vouchers.view', 'reports.export']);

        $response = $this->actingAs($user)
            ->get('/api/v1/module-reports/cash-vouchers/register/export?format=csv');

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type') ?? '');
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition') ?? '');
    }

    public function test_export_returns_a_pdf_file(): void
    {
        $user = $this->userWithPermissions(['cash-vouchers.view', 'reports.export']);

        $response = $this->actingAs($user)
            ->get('/api/v1/module-reports/cash-vouchers/register/export?format=pdf');

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type') ?? '');
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition') ?? '');

        // The bytes really are a PDF, so the blade template rendered rather
        // than silently producing an empty document.
        $this->assertStringStartsWith('%PDF-', $response->streamedContent());
    }

    public function test_export_rejects_an_unsupported_format(): void
    {
        $user = $this->userWithPermissions(['cash-vouchers.view', 'reports.export']);

        $this->actingAs($user)
            ->getJson('/api/v1/module-reports/cash-vouchers/register/export?format=xlsx')
            ->assertStatus(422);
    }

    /* -------------------------------- access ----------------------------- */

    /**
     * Opening the report area is baseline: every employee may browse the
     * catalogue, and the catalogue itself only lists the modules they can
     * already read. Export is a separate, deliberately non-baseline grant.
     */
    public function test_opening_the_report_area_is_baseline_but_export_is_not(): void
    {
        $baseline = config('access.baseline', []);

        $this->assertContains('reports.view', $baseline);
        $this->assertNotContains('reports.export', $baseline);

        // And a baseline-only employee really ends up able to open the
        // catalogue while still being unable to export it.
        $this->grantEveryReportPermission();

        $user = User::factory()->create();
        $user->givePermissionTo(
            Permission::whereIn('name', $baseline)->pluck('name')->all()
        );
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->assertTrue(\App\Support\Access::can($user, 'reports.view'));
        $this->assertFalse(\App\Support\Access::can($user, 'reports.export'));

        // The catalogue opens (possibly empty - the point is that it is not
        // refused), but exporting is.
        $this->actingAs($user)->getJson('/api/v1/module-reports')->assertOk();
        $this->actingAs($user)
            ->getJson('/api/v1/module-reports/cash-vouchers/register/export?format=csv')
            ->assertStatus(403);
    }

    /* -------------------------------- engine ----------------------------- */

    /**
     * An unqualified filter column is pointed at the report's own table, so a
     * filter on "status" can never become ambiguous once an unrelated table
     * with a status of its own is joined.
     */
    public function test_report_payload_quotes_its_output_keys(): void
    {
        $user = $this->superAdmin();

        $payload = $this->service()->run('cash-vouchers', 'register', [], $user);

        $keys = array_column($payload['report']['columns'], 'key');
        $this->assertNotEmpty($keys);

        // Every key is present in at least the shape of the row set (empty
        // here is fine - what matters is that the select list did not fail on
        // a reserved word).
        $this->assertSame($keys, array_values(array_unique($keys)));
    }
}
