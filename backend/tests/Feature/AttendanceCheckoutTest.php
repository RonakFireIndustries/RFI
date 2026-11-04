<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Site;
use App\Models\User;
use App\Support\Access;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Nearest-site checkout: when an employee checks out from anywhere, the
 * nearest active site is resolved from GPS coordinates and recorded;
 * checkout is never blocked by radius.
 *
 * Bulk site assignment: an employee can be assigned to several sites at
 * once; already-assigned sites are skipped and one history row is written
 * per newly assigned site.
 */
class AttendanceCheckoutTest extends TestCase
{
    private const MIGRATIONS = [
        '0001_01_01_000000_create_users_table.php',
        '2026_05_30_083453_create_permission_tables.php',
        '2026_08_27_000000_add_is_super_admin_to_users_table.php',
        '2026_05_30_083454_create_employees_table.php',
        '2026_06_17_074528_create_shifts_table.php',
        '2026_10_13_000001_create_employee_shifts_table.php',
        '2026_06_16_000001_create_sites_table.php',
        '2026_06_16_000002_create_employee_sites_table.php',
        '2026_06_17_065624_add_details_to_sites_table.php',
        '2026_06_18_000001_add_allowed_radius_to_sites_table.php',
        '2026_06_17_071821_rename_columns_in_employee_sites_table.php',
        '2026_06_17_072006_make_role_column_nullable_in_employee_sites_table.php',
        '2026_05_30_090647_create_attendances_table.php',
        '2026_06_17_074532_add_details_to_attendances_table.php',
        '2026_06_18_000002_add_gps_fields_to_attendances_table.php',
        '2026_08_19_081828_add_is_late_to_attendances_table.php',
        '2026_10_14_000002_add_is_auto_checkout_to_attendances_table.php',
        '2026_06_17_065624_create_employee_site_histories_table.php',
        '2026_11_04_000001_add_checkout_site_id_to_attendances_table.php',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        foreach (self::MIGRATIONS as $file) {
            $migration = require __DIR__ . '/../../database/migrations/' . $file;
            $migration->up();
        }

        foreach (Access::allPermissionNames() as $name) {
            Permission::findOrCreate($name, 'web');
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // The Employee model uses SoftDeletes; the base employees migration
        // predates it, so add the column for the test database.
        Schema::table('employees', function ($table) {
            $table->softDeletes();
        });

        // The Attendance booted() saving callback sets $attendance->overtime;
        // that column is not in the base attendances migrations, so add it.
        Schema::table('attendances', function ($table) {
            $table->decimal('overtime', 8, 2)->default(0.00);
        });
    }

    private function superAdmin(): User
    {
        return User::factory()->create(['is_super_admin' => true]);
    }

    /** A user with an employee profile. */
    private function employeeUser(): User
    {
        $user = User::factory()->create();
        Employee::create([
            'user_id' => $user->id,
            'status' => 'active',
        ]);

        return $user;
    }

    private function site(array $attrs = []): Site
    {
        return Site::create(array_merge([
            'name' => 'Site ' . fake()->unique()->word(),
            'code' => fake()->unique()->numerify('S###'),
            'status' => 'Active',
            'latitude' => 28.6139,
            'longitude' => 77.2090,
            'allowed_radius' => 100,
        ], $attrs));
    }

    /** Check the employee in at the assigned site's coordinates. */
    private function checkIn(User $user): void
    {
        $this->actingAs($user);
        $this->postJson('/api/v1/attendance/check-in', [
            'latitude' => 28.6139,
            'longitude' => 77.2090,
            'accuracy' => 10,
        ])->assertOk();
    }

    /* ----------------------------------- checkout ---------------------------------- */

    public function test_checkout_resolves_nearest_active_site_even_when_unassigned(): void
    {
        $user = $this->employeeUser();
        $employee = $user->employee;

        // Assigned site is further away (~122m from the checkout point).
        $assigned = $this->site([
            'name' => 'Assigned Site',
            'latitude' => 28.6139,
            'longitude' => 77.2090,
        ]);
        $employee->employeeSites()->create([
            'site_id' => $assigned->id,
            'assigned_at' => now(),
        ]);

        // Unassigned site is right at the checkout point (~0m).
        $nearest = $this->site([
            'name' => 'Nearest Site',
            'latitude' => 28.6150,
            'longitude' => 77.2090,
        ]);

        $this->checkIn($user);

        $response = $this->postJson('/api/v1/attendance/check-out', [
            'latitude' => 28.6150,
            'longitude' => 77.2090,
            'accuracy' => 10,
        ]);

        $response->assertOk();
        $data = $response->json('data');

        $this->assertSame($nearest->id, $data['checkout_site']['id']);
        $this->assertSame('Nearest Site', $data['checkout_site']['name']);
        $this->assertLessThan(5, $data['checkout_distance']);
        // The check-in site on the attendance record is unchanged.
        $this->assertSame($assigned->id, $data['attendance']['site_id']);
        // But the checkout site reflects the nearest active site, not the assigned one.
        $this->assertSame($nearest->id, $data['attendance']['checkout_site_id']);
    }

    public function test_checkout_succeeds_outside_every_radius_but_still_records_nearest_site(): void
    {
        $user = $this->employeeUser();
        $employee = $user->employee;

        $assigned = $this->site([
            'name' => 'Assigned Site',
            'latitude' => 28.6139,
            'longitude' => 77.2090,
        ]);
        $employee->employeeSites()->create([
            'site_id' => $assigned->id,
            'assigned_at' => now(),
        ]);

        $this->checkIn($user);

        // ~438m from the assigned site — outside the 100m radius.
        $response = $this->postJson('/api/v1/attendance/check-out', [
            'latitude' => 28.6139,
            'longitude' => 77.2135,
            'accuracy' => 10,
        ]);

        $response->assertOk();
        $data = $response->json('data');

        // Checkout is never blocked.
        $this->assertTrue($data['location_verified'] === false);
        $this->assertNotNull($data['checkout_site']);
        $this->assertNotNull($data['checkout_distance']);
        $this->assertGreaterThan(100, $data['checkout_distance']);
    }

    public function test_checkout_within_radius_is_verified(): void
    {
        $user = $this->employeeUser();
        $employee = $user->employee;

        $assigned = $this->site([
            'name' => 'Assigned Site',
            'latitude' => 28.6139,
            'longitude' => 77.2090,
            'allowed_radius' => 100,
        ]);
        $employee->employeeSites()->create([
            'site_id' => $assigned->id,
            'assigned_at' => now(),
        ]);

        $this->checkIn($user);

        // Checking out from the assigned site's own coordinates (~0m).
        $response = $this->postJson('/api/v1/attendance/check-out', [
            'latitude' => 28.6139,
            'longitude' => 77.2090,
            'accuracy' => 10,
        ]);

        $response->assertOk();
        $data = $response->json('data');

        $this->assertTrue($data['location_verified']);
        $this->assertSame($assigned->id, $data['checkout_site']['id']);
        $this->assertLessThan(5, $data['checkout_distance']);
    }

    public function test_checkout_requires_check_in_first(): void
    {
        $user = $this->employeeUser();

        $this->actingAs($user);
        $this->postJson('/api/v1/attendance/check-out', [
            'latitude' => 28.6139,
            'longitude' => 77.2090,
        ])->assertStatus(400)
         ->assertJson(['message' => 'Must check in first']);
    }

    /* --------------------------------- bulk assignment -------------------------------- */

    public function test_bulk_assign_creates_one_history_row_per_new_site(): void
    {
        $admin = $this->superAdmin();
        $user = $this->employeeUser();
        $employee = $user->employee;

        $siteA = $this->site(['name' => 'Site A']);
        $siteB = $this->site(['name' => 'Site B']);

        $this->actingAs($admin);
        $response = $this->postJson("/api/v1/employees/{$employee->id}/sites/bulk", [
            'site_ids' => [$siteA->id, $siteB->id],
        ]);

        $response->assertStatus(201);
        $this->assertSame(2, $response->json('data.assigned_count'));

        // One history row per newly assigned site.
        $this->assertCount(2, $employee->siteHistories);
        $this->assertCount(2, $employee->employeeSites);
    }

    public function test_bulk_assign_skips_already_assigned_sites(): void
    {
        $admin = $this->superAdmin();
        $user = $this->employeeUser();
        $employee = $user->employee;

        $siteA = $this->site(['name' => 'Site A']);
        $siteB = $this->site(['name' => 'Site B']);

        // Pre-assign site A.
        $employee->employeeSites()->create([
            'site_id' => $siteA->id,
            'assigned_at' => now(),
        ]);

        $this->actingAs($admin);
        $response = $this->postJson("/api/v1/employees/{$employee->id}/sites/bulk", [
            'site_ids' => [$siteA->id, $siteB->id],
        ]);

        $response->assertStatus(201);
        // Only site B is new; site A is skipped.
        $this->assertSame(1, $response->json('data.assigned_count'));
        $this->assertCount(1, $employee->siteHistories);
        $this->assertCount(2, $employee->employeeSites); // A (pre-existing) + B (new)
    }

    public function test_bulk_assign_re_submitting_same_sites_is_a_no_op(): void
    {
        $admin = $this->superAdmin();
        $user = $this->employeeUser();
        $employee = $user->employee;

        $siteA = $this->site(['name' => 'Site A']);

        $employee->employeeSites()->create([
            'site_id' => $siteA->id,
            'assigned_at' => now(),
        ]);

        $this->actingAs($admin);
        $this->postJson("/api/v1/employees/{$employee->id}/sites/bulk", [
            'site_ids' => [$siteA->id],
        ])->assertStatus(201);

        // Re-submitting the same site creates no additional records.
        $this->postJson("/api/v1/employees/{$employee->id}/sites/bulk", [
            'site_ids' => [$siteA->id],
        ])->assertStatus(201);

        $this->assertCount(0, $employee->siteHistories);
        $this->assertCount(1, $employee->employeeSites);
    }
}
