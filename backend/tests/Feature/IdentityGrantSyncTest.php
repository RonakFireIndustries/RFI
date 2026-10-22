<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\User;
use App\Support\Access;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * The identity-grant sync (php artisan access:sync-grants).
 *
 * config/access.php decides "which department / designation gets what". A new
 * module is not usable until those grants actually reach the matching people,
 * and the only command that applied them (employees:sync-roles) also detaches
 * every Spatie role as a side effect. This suite covers the additive command
 * that ships the grants without that side effect.
 *
 * Only the migrations these tests touch are run - the full chain is
 * MySQL-specific in places, as with the other suites.
 */
class IdentityGrantSyncTest extends TestCase
{
    private const MIGRATIONS = [
        '0001_01_01_000000_create_users_table.php',
        '2026_05_30_083453_create_permission_tables.php',
        '2026_08_27_000000_add_is_super_admin_to_users_table.php',
        '2026_05_30_083454_create_branches_table.php',
        '2026_05_30_083454_create_employees_table.php',
        '2026_06_16_000004_create_departments_table.php',
        '2026_06_16_000005_create_designations_table.php',
        '2026_06_16_000006_add_business_relationship_columns.php',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        foreach (self::MIGRATIONS as $file) {
            $migration = require __DIR__ . '/../../database/migrations/' . $file;
            $migration->up();
        }

        $this->addDriftedColumns();

        foreach (Access::allPermissionNames() as $name) {
            Permission::findOrCreate($name, 'web');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * The employees / departments / designations tables have drifted ahead of
     * their migrations on a real install - the soft-delete columns and the
     * employee name columns were added by hand and no migration creates them
     * (the 2026_10_15_000001 migration says so in as many words). Every model
     * here uses SoftDeletes, so the columns have to exist before the models
     * will even load.
     */
    private function addDriftedColumns(): void
    {
        foreach (['employees', 'departments', 'designations'] as $table) {
            if (!Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->timestamp('deleted_at')->nullable();
                });
            }
        }

        if (!Schema::hasColumn('employees', 'emp_id')) {
            Schema::table('employees', function (Blueprint $blueprint) {
                $blueprint->string('emp_id')->nullable();
            });
        }

        if (!Schema::hasColumn('employees', 'full_name')) {
            Schema::table('employees', function (Blueprint $blueprint) {
                $blueprint->string('full_name')->nullable();
            });
        }
    }

    /* ------------------------------ fixtures ----------------------------- */

    private function branch(): void
    {
        if (DB::table('branches')->where('id', 1)->exists()) {
            return;
        }

        DB::table('branches')->insert([
            'id' => 1,
            'name' => 'Head Office',
            'location' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function department(string $name): Department
    {
        $this->branch();

        // branch_id is not mass-assignable, but the column is NOT NULL.
        $department = new Department(['name' => $name]);
        $department->branch_id = 1;
        $department->status = 'Active';
        $department->save();

        return $department;
    }

    private function designation(string $name, string $department = 'Administration'): Designation
    {
        $this->branch();

        $designation = new Designation(['name' => $name]);
        $designation->branch_id = 1;
        $designation->status = 'Active';
        $designation->save();

        return $designation;
    }

    /**
     * A user with an employee record, so the identity match has a
     * designation and a department to look at.
     */
    private function employee(
        string $email,
        ?Designation $designation = null,
        ?Department $department = null,
        bool $superAdmin = false,
    ): User {
        $user = User::factory()->create([
            'email' => $email,
            'is_super_admin' => $superAdmin,
        ]);

        Employee::create([
            'user_id' => $user->id,
            'emp_id' => 'E' . $user->id,
            'full_name' => $email,
            'designation_id' => $designation?->id,
            'department_id' => $department?->id,
        ]);

        return $user;
    }

    /** Replace the identity grants with a small, predictable set. */
    private function useTestGrants(): void
    {
        config(['access.identity_grants' => [
            'Administration' => [
                'designations' => ['Admin'],
                'departments' => ['Administration'],
                'permissions' => ['holidays.view', 'holidays.create', 'holidays.update'],
            ],
            'Human Resources' => [
                'designations' => ['HR'],
                'departments' => [],
                'permissions' => ['holidays.view', 'holidays.create'],
            ],
        ]]);
    }

    /* -------------------------------- grants ------------------------------ */

    public function test_it_grants_the_identity_permissions_to_a_matching_employee(): void
    {
        $this->useTestGrants();

        $admin = $this->employee(
            'admin@example.test',
            $this->designation('Admin'),
            $this->department('Administration'),
        );

        $this->artisan('access:sync-grants')->assertSuccessful();

        $held = $admin->getDirectPermissions()->pluck('name');
        $this->assertTrue($held->contains('holidays.view'));
        $this->assertTrue($held->contains('holidays.create'));
        $this->assertTrue($held->contains('holidays.update'));
    }

    public function test_it_matches_on_department_as_well_as_designation(): void
    {
        $this->useTestGrants();

        // A Designer in the Administration department matches on department.
        $designer = $this->employee(
            'designer@example.test',
            $this->designation('Designer'),
            $this->department('Administration'),
        );

        $this->artisan('access:sync-grants')->assertSuccessful();

        $this->assertTrue($designer->getDirectPermissions()->pluck('name')->contains('holidays.view'));
    }

    /**
     * The employees table also carries a `department` string column from the
     * original create_employees_table migration, and an attribute of that name
     * shadows the department() relation: $employee->department returns the
     * string, not the Department. Matching through the plain attribute made
     * every department-based grant silently match nothing on a fresh install
     * (the production table has drifted and no longer has the column, which
     * is why it went unnoticed there).
     */
    public function test_it_matches_the_department_relation_not_the_shadowing_column(): void
    {
        $this->useTestGrants();

        $this->assertTrue(
            Schema::hasColumn('employees', 'department'),
            'The employees table is expected to still carry the shadowing department column.'
        );

        $designer = $this->employee(
            'designer@example.test',
            $this->designation('Designer'),
            $this->department('Administration'),
        );

        // The attribute shadows the relation...
        $this->assertNull($designer->employee->department);
        // ...while the relation is what the match has to use.
        $this->assertSame(
            'Administration',
            $designer->employee->getRelationValue('department')->name
        );

        $this->artisan('access:sync-grants')->assertSuccessful();

        $this->assertTrue($designer->getDirectPermissions()->pluck('name')->contains('holidays.view'));
    }

    public function test_it_leaves_an_employee_outside_every_group_alone(): void
    {
        $this->useTestGrants();

        $welder = $this->employee(
            'welder@example.test',
            $this->designation('Welder'),
            $this->department('Production'),
        );

        $this->artisan('access:sync-grants')->assertSuccessful();

        $this->assertSame(0, $welder->getDirectPermissions()->count());
    }

    public function test_it_matches_names_exactly_and_not_by_substring(): void
    {
        $this->useTestGrants();

        // "Administrator" contains "Admin" - a substring match would promote
        // this employee into the Administration group.
        $other = $this->employee(
            'other@example.test',
            $this->designation('Administrator'),
            $this->department('Production'),
        );

        $this->artisan('access:sync-grants')->assertSuccessful();

        $this->assertSame(0, $other->getDirectPermissions()->count());
    }

    public function test_it_never_revokes_a_grant_it_did_not_make(): void
    {
        $this->useTestGrants();

        $admin = $this->employee(
            'admin@example.test',
            $this->designation('Admin'),
            $this->department('Administration'),
        );
        $admin->givePermissionTo('reports.view');

        $this->artisan('access:sync-grants')->assertSuccessful();

        $held = $admin->getDirectPermissions()->pluck('name');
        $this->assertTrue($held->contains('reports.view'), 'An unrelated grant must survive the sync.');
        $this->assertTrue($held->contains('holidays.view'));
    }

    public function test_it_leaves_roles_alone(): void
    {
        $this->useTestGrants();

        $admin = $this->employee(
            'admin@example.test',
            $this->designation('Admin'),
            $this->department('Administration'),
        );

        $role = Role::findOrCreate('Store Manager', 'web');
        $admin->assignRole($role);

        $this->artisan('access:sync-grants')->assertSuccessful();

        // The whole point of this command: the grants go out without the
        // role-detaching migration employees:sync-roles performs.
        $this->assertTrue($admin->fresh()->hasRole('Store Manager'));
    }

    public function test_it_skips_a_super_admin(): void
    {
        $this->useTestGrants();

        $super = $this->employee(
            'root@example.test',
            $this->designation('Welder'),
            $this->department('Production'),
            superAdmin: true,
        );

        $this->artisan('access:sync-grants')->assertSuccessful();

        $this->assertSame(0, $super->getDirectPermissions()->count());
    }

    public function test_it_can_be_narrowed_to_one_module(): void
    {
        config(['access.identity_grants' => [
            'Administration' => [
                'designations' => ['Admin'],
                'departments' => [],
                'permissions' => ['holidays.view', 'holidays.create', 'reports.view'],
            ],
        ]]);

        $admin = $this->employee(
            'admin@example.test',
            $this->designation('Admin'),
            $this->department('Administration'),
        );

        $this->artisan('access:sync-grants --module=holidays')->assertSuccessful();

        $held = $admin->getDirectPermissions()->pluck('name');
        $this->assertTrue($held->contains('holidays.view'));
        $this->assertFalse($held->contains('reports.view'), 'A different module must not be granted.');
    }

    public function test_a_dry_run_changes_nothing(): void
    {
        $this->useTestGrants();

        $admin = $this->employee(
            'admin@example.test',
            $this->designation('Admin'),
            $this->department('Administration'),
        );

        $this->artisan('access:sync-grants --dry-run')->assertSuccessful();

        $this->assertSame(0, $admin->fresh()->getDirectPermissions()->count());
    }

    public function test_it_is_idempotent(): void
    {
        $this->useTestGrants();

        $admin = $this->employee(
            'admin@example.test',
            $this->designation('Admin'),
            $this->department('Administration'),
        );

        $this->artisan('access:sync-grants')->assertSuccessful();
        $after = $admin->fresh()->getDirectPermissions()->pluck('name')->sort()->values()->all();

        $this->artisan('access:sync-grants')->assertSuccessful();
        $again = $admin->fresh()->getDirectPermissions()->pluck('name')->sort()->values()->all();

        $this->assertSame($after, $again);
    }

    /* --------------------------- the shipped config ----------------------- */

    /**
     * The reason the calendar was unreachable: the module was declared and
     * the permission rows were seeded, but the Administration group only
     * listed holidays.view - and the Human Resources group matched nobody,
     * so no one in the organisation could even create a holiday.
     */
    public function test_the_shipped_config_lets_administrators_manage_the_calendar(): void
    {
        $groups = config('access.identity_grants', []);

        $administration = collect($groups)->firstWhere(
            fn($group) => in_array('Admin', $group['designations'] ?? [], true)
        );

        $this->assertNotNull($administration, 'config/access.php should have an Administration identity group.');

        $permissions = $administration['permissions'] ?? [];
        $this->assertContains('holidays.view', $permissions);
        $this->assertContains('holidays.create', $permissions);
        $this->assertContains('holidays.update', $permissions);

        // Deleting a holiday is deliberately not granted: retiring it
        // (status -> Inactive) is the reversible path.
        $this->assertNotContains('holidays.delete', $permissions);
    }
}
