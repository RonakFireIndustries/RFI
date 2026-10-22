<?php

namespace Tests\Feature;

use App\Models\Holiday;
use App\Models\User;
use App\Services\LeaveRequestService;
use App\Support\Access;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * The company holiday calendar.
 *
 * A holiday declared here is what payroll, attendance and leave read to
 * decide which days are working days, so these tests cover both halves of the
 * feature: the CRUD contract, and the rule that only an Active holiday
 * shortens the working month.
 *
 * The application's full migration chain is MySQL-specific in places
 * (SHOW KEYS / ALTER ... MODIFY), so - as AccountingTest does - only the
 * migrations these endpoints actually need are run against the SQLite test
 * database.
 */
class HolidayTest extends TestCase
{
    /** Migrations required by the holiday endpoints, auth and permissions. */
    private const MIGRATIONS = [
        '0001_01_01_000000_create_users_table.php',
        '2026_05_30_083453_create_permission_tables.php',
        '2026_08_27_000000_add_is_super_admin_to_users_table.php',
        '2026_06_17_125213_create_holidays_table.php',
        '2026_10_22_000001_enhance_holidays_table.php',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        foreach (self::MIGRATIONS as $file) {
            $migration = require __DIR__ . '/../../database/migrations/' . $file;
            $migration->up();
        }

        // The canonical permission rows have to exist before the gate can
        // answer for anyone - super admin included.
        foreach (Access::allPermissionNames() as $name) {
            Permission::findOrCreate($name, 'web');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /* ------------------------------ fixtures ----------------------------- */

    private function superAdmin(): User
    {
        return User::factory()->create(['is_super_admin' => true]);
    }

    /** A plain user who holds the listed canonical permissions. */
    private function userWithPermissions(array $names): User
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $user = User::factory()->create();
        $user->givePermissionTo($names);

        return $user;
    }

    private function holiday(array $attributes = []): Holiday
    {
        return Holiday::create(array_merge([
            'name' => 'Republic Day',
            'date' => '2026-01-26',
            'type' => Holiday::TYPE_PUBLIC,
            'status' => Holiday::STATUS_ACTIVE,
        ], $attributes));
    }

    /* ------------------------------- reading ----------------------------- */

    public function test_it_lists_the_company_holiday_calendar(): void
    {
        $this->actingAs($this->superAdmin());

        $this->holiday();
        $this->holiday(['name' => 'Founders Day', 'date' => '2026-04-01']);

        $response = $this->getJson('/api/v1/holidays');

        $response->assertOk();
        $response->assertJsonCount(2, 'data.holidays');
        $this->assertSame('Republic Day', $response->json('data.holidays.0.name'));
        // The date travels as a plain Y-m-d string so the calendar day never
        // shifts for a client west of Greenwich.
        $this->assertSame('2026-01-26', $response->json('data.holidays.0.date'));
        $this->assertSame('Monday', $response->json('data.holidays.0.day_of_week'));
    }

    public function test_it_filters_the_calendar_by_year_type_and_status(): void
    {
        $this->actingAs($this->superAdmin());

        $this->holiday(['name' => 'Republic Day', 'date' => '2026-01-26']);
        $this->holiday(['name' => 'Founders Day', 'date' => '2026-04-01', 'type' => Holiday::TYPE_OPTIONAL]);
        $this->holiday(['name' => 'Old Holiday', 'date' => '2025-12-25']);
        $this->holiday(['name' => 'Cancelled', 'date' => '2026-05-01', 'status' => Holiday::STATUS_INACTIVE]);

        $this->getJson('/api/v1/holidays?year=2026')
            ->assertOk()
            ->assertJsonCount(3, 'data.holidays');

        $this->getJson('/api/v1/holidays?year=2025')
            ->assertOk()
            ->assertJsonCount(1, 'data.holidays');

        $this->getJson('/api/v1/holidays?type=' . urlencode(Holiday::TYPE_OPTIONAL))
            ->assertOk()
            ->assertJsonCount(1, 'data.holidays');

        $this->getJson('/api/v1/holidays?status=Inactive')
            ->assertOk()
            ->assertJsonCount(1, 'data.holidays');

        $this->getJson('/api/v1/holidays?search=Founders')
            ->assertOk()
            ->assertJsonCount(1, 'data.holidays');
    }

    public function test_it_lists_the_years_on_the_calendar(): void
    {
        $this->actingAs($this->superAdmin());

        $this->holiday(['date' => '2026-01-26']);
        $this->holiday(['name' => 'Christmas', 'date' => '2025-12-25']);

        $response = $this->getJson('/api/v1/holidays/years');

        $response->assertOk();
        $years = $response->json('data.years');

        $this->assertContains(2025, $years);
        $this->assertContains(2026, $years);
        // The current year is always offered, so the calendar of a year that
        // has not been announced yet can still be opened and filled in.
        $this->assertContains((int) now()->year, $years);
    }

    /* ------------------------------- writing ----------------------------- */

    public function test_it_creates_a_holiday(): void
    {
        $this->actingAs($this->superAdmin());

        $response = $this->postJson('/api/v1/holidays', [
            'name' => 'Independence Day',
            'date' => '2026-08-15',
            'type' => Holiday::TYPE_PUBLIC,
            'description' => 'All offices closed.',
        ]);

        $response->assertStatus(201);
        $this->assertSame('Independence Day', $response->json('data.holiday.name'));
        $this->assertSame('All offices closed.', $response->json('data.holiday.description'));

        $this->assertDatabaseHas('holidays', [
            'name' => 'Independence Day',
            'status' => Holiday::STATUS_ACTIVE,
        ]);
        $this->assertDatabaseHas('holidays', ['name' => 'Independence Day']);
    }

    public function test_it_folds_a_type_alias_onto_the_canonical_label(): void
    {
        $this->actingAs($this->superAdmin());

        $this->postJson('/api/v1/holidays', [
            'name' => 'Diwali',
            'date' => '2026-11-08',
            'type' => 'Optional',
        ])->assertStatus(201);

        $this->assertDatabaseHas('holidays', [
            'name' => 'Diwali',
            'type' => Holiday::TYPE_OPTIONAL,
        ]);
    }

    public function test_it_refuses_two_holidays_on_the_same_date(): void
    {
        $this->actingAs($this->superAdmin());

        $this->holiday(['date' => '2026-01-26']);

        $this->postJson('/api/v1/holidays', [
            'name' => 'Another Republic Day',
            'date' => '2026-01-26',
            'type' => Holiday::TYPE_PUBLIC,
        ])->assertStatus(422);
    }

    public function test_it_rejects_a_type_that_is_not_on_the_calendar(): void
    {
        $this->actingAs($this->superAdmin());

        $this->postJson('/api/v1/holidays', [
            'name' => 'Something',
            'date' => '2026-01-26',
            'type' => 'Invented Holiday',
        ])->assertStatus(422);
    }

    public function test_it_updates_a_holiday(): void
    {
        $this->actingAs($this->superAdmin());

        $holiday = $this->holiday();

        $this->putJson("/api/v1/holidays/{$holiday->id}", [
            'name' => 'Republic Day (revised)',
            'date' => '2026-01-26',
            'type' => Holiday::TYPE_RESTRICTED,
            'status' => Holiday::STATUS_INACTIVE,
        ])->assertOk();

        $this->assertDatabaseHas('holidays', [
            'id' => $holiday->id,
            'name' => 'Republic Day (revised)',
            'type' => Holiday::TYPE_RESTRICTED,
            'status' => Holiday::STATUS_INACTIVE,
        ]);
    }

    public function test_it_deletes_a_holiday(): void
    {
        $this->actingAs($this->superAdmin());

        $holiday = $this->holiday();

        $this->deleteJson("/api/v1/holidays/{$holiday->id}")->assertOk();

        $this->assertDatabaseMissing('holidays', ['id' => $holiday->id]);
    }

    /* ---------------------------- authorisation -------------------------- */

    public function test_it_refuses_a_reader_who_may_not_see_the_calendar(): void
    {
        $this->actingAs($this->userWithPermissions(['daily-reports.view']));

        $this->getJson('/api/v1/holidays')->assertStatus(403);
    }

    public function test_it_refuses_a_writer_who_may_not_add_a_holiday(): void
    {
        $this->actingAs($this->userWithPermissions(['holidays.view']));

        $this->postJson('/api/v1/holidays', [
            'name' => 'Republic Day',
            'date' => '2026-01-26',
            'type' => Holiday::TYPE_PUBLIC,
        ])->assertStatus(403);
    }

    public function test_it_refuses_a_deleter_who_may_not_remove_a_holiday(): void
    {
        $this->actingAs($this->userWithPermissions(['holidays.view', 'holidays.update']));

        $holiday = $this->holiday();

        $this->deleteJson("/api/v1/holidays/{$holiday->id}")->assertStatus(403);
    }

    /* --------------------- only Active holidays count -------------------- */

    public function test_only_an_active_holiday_shortens_the_working_month(): void
    {
        // Mon 2026-01-05 .. Fri 2026-01-09 - five working days.
        $this->holiday(['name' => 'Active', 'date' => '2026-01-07']);
        $this->holiday([
            'name' => 'Retired',
            'date' => '2026-01-08',
            'status' => Holiday::STATUS_INACTIVE,
        ]);

        $days = app(LeaveRequestService::class)->calculateDays('2026-01-05', '2026-01-09', false);

        // The active holiday takes one day out; the retired one does not.
        $this->assertLessThan(5.0, (float) $days);
        $this->assertSame(4.0, (float) $days);
    }

    public function test_the_active_scope_leaves_a_retired_holiday_out(): void
    {
        $this->holiday(['name' => 'Active', 'date' => '2026-01-07']);
        $this->holiday([
            'name' => 'Retired',
            'date' => '2026-01-08',
            'status' => Holiday::STATUS_INACTIVE,
        ]);

        $this->assertSame(1, Holiday::active()->count());
        $this->assertSame(2, Holiday::query()->count());
    }
}
