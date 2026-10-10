<?php

namespace Tests\Feature;

use App\Models\Building;
use App\Models\Site;
use App\Services\BuildingConversionService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Covers promoting a survey building into a working site.
 *
 * The three new migrations are exercised directly against a hand-built schema
 * that mirrors the production column set, so both the DDL and the conversion
 * logic are covered without depending on the full application migration chain.
 */
class BuildingConversionTest extends TestCase
{
    private BuildingConversionService $conversion;

    protected function setUp(): void
    {
        parent::setUp();

        $this->conversion = new BuildingConversionService();

        $this->buildSitesTable();
        $this->buildBuildingsTable();
        $this->buildChildTables();

        $this->runMigration('2026_10_03_000001_add_building_profile_to_sites_table.php');
        $this->runMigration('2026_10_03_000002_add_site_id_to_building_children_tables.php');
        $this->runMigration('2026_10_03_000003_add_conversion_tracking_to_buildings_table.php');
    }

    private function runMigration(string $file): void
    {
        $migration = require __DIR__ . '/../../database/migrations/' . $file;
        $migration->up();
    }

    /** Only the columns the conversion reads or writes. */
    private function buildSitesTable(): void
    {
        Schema::create('sites', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();
            $t->string('code')->nullable();
            $t->string('client_details')->nullable();
            $t->text('address')->nullable();
            $t->string('city')->nullable();
            $t->string('state')->nullable();
            $t->string('country')->nullable();
            $t->string('pincode')->nullable();
            $t->string('contact_person')->nullable();
            $t->string('phone')->nullable();
            $t->string('email')->nullable();
            $t->decimal('latitude', 10, 8)->nullable();
            $t->decimal('longitude', 11, 8)->nullable();
            $t->unsignedBigInteger('site_manager_id')->nullable();
            $t->string('status')->default('Active');
            $t->timestamps();
        });
    }

    private function buildBuildingsTable(): void
    {
        Schema::create('buildings', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('society_name')->nullable();
            $t->string('building_type')->nullable();
            $t->text('address')->nullable();
            $t->string('area')->nullable();
            $t->string('city')->nullable();
            $t->string('state')->nullable();
            $t->string('country')->nullable()->default('India');
            $t->string('pincode')->nullable();
            $t->string('google_place_id')->nullable();
            $t->decimal('latitude', 10, 8)->nullable();
            $t->decimal('longitude', 11, 8)->nullable();
            $t->integer('construction_year')->nullable();
            $t->integer('occupancy_certificate_year')->nullable();
            $t->string('fire_noc_status')->default('Unknown');
            $t->unsignedInteger('no_of_floors')->nullable();
            $t->integer('basement_levels')->nullable();
            $t->string('no_of_wings')->nullable();
            $t->unsignedInteger('no_of_flats')->nullable();
            $t->boolean('terrace_available')->default(false);
            $t->integer('refuge_floors')->nullable();
            $t->boolean('commercial_shops_available')->default(false);
            $t->unsignedInteger('no_of_staircase')->nullable();
            $t->unsignedInteger('no_of_lifts')->nullable();
            $t->unsignedInteger('no_of_exits_entry')->nullable();
            $t->boolean('fire_safety_available')->default(false);
            $t->string('fire_safety_type')->nullable();
            $t->text('equipment_condition_notes')->nullable();
            $t->date('last_serviced_date')->nullable();
            $t->string('last_amc_company')->nullable();
            $t->string('overall_equipment_condition')->nullable();
            $t->boolean('under_construction')->default(false);
            $t->string('property_owner')->nullable();
            $t->string('plot_no')->nullable();
            $t->string('contact_name')->nullable();
            $t->string('contact_number')->nullable();
            $t->string('developer_name')->nullable();
            $t->string('developer_contact')->nullable();
            $t->string('architect_name')->nullable();
            $t->string('architect_contact')->nullable();
            $t->string('pmc_consultant_name')->nullable();
            $t->string('pmc_consultant_contact')->nullable();
            $t->boolean('provisional_noc')->default(false);
            $t->text('provisional_noc_details')->nullable();
            $t->unsignedBigInteger('site_id')->nullable();
            $t->string('status')->default('Active');
            $t->timestamps();
        });
    }

    private function buildChildTables(): void
    {
        // Every table the service mirrors onto, so the backfill is covered.
        $childTables = [
            'building_wings',
            'building_floors',
            'building_flats',
            'building_amcs',
            'building_fire_systems',
            'fire_systems',
            'building_contacts',
            'building_status_mapping',
        ];

        foreach ($childTables as $table) {
            Schema::create($table, function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('building_id');
                $t->timestamps();
            });
        }
    }

    private function makeBuilding(array $overrides = []): Building
    {
        return Building::create(array_merge([
            'name' => 'Sunrise Residency',
            'society_name' => 'Sunrise Co-op Housing Society',
            'building_type' => 'Residential',
            'address' => '12 Marine Drive',
            'area' => 'Andheri West',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'country' => 'India',
            'pincode' => '400058',
            'latitude' => 19.1197,
            'longitude' => 72.8464,
            'no_of_floors' => 18,
            'no_of_wings' => 2,
            'no_of_flats' => 144,
            'fire_safety_available' => true,
            'fire_safety_type' => 'Sprinkler + Hydrant',
            'fire_noc_status' => 'Approved',
            'construction_year' => 2019,
            'contact_name' => 'Ramesh Iyer',
            'contact_number' => '+91 98200 00000',
            'developer_name' => 'Kohinoor Developers',
            'property_owner' => 'Sunrise CHS Ltd',
            'plot_no' => 'A-112',
            'status' => 'Active',
        ], $overrides));
    }

    public function test_it_creates_a_site_carrying_the_full_survey_profile(): void
    {
        $building = $this->makeBuilding();

        $result = $this->conversion->convert($building);

        $this->assertTrue($result['created']);
        $this->assertFalse($result['already_converted']);

        $site = $result['site'];
        $this->assertSame('Sunrise Residency', $site->name);

        // Shared columns.
        $this->assertSame('12 Marine Drive', $site->address);
        $this->assertSame('Mumbai', $site->city);
        $this->assertSame('Maharashtra', $site->state);
        $this->assertSame('400058', $site->pincode);
        $this->assertEquals(19.1197, (float) $site->latitude);
        $this->assertSame('Active', $site->status);

        // Survey-only columns.
        $this->assertSame('Sunrise Co-op Housing Society', $site->society_name);
        $this->assertSame(18, $site->no_of_floors);
        $this->assertSame(144, $site->no_of_flats);
        $this->assertSame('Approved', $site->fire_noc_status);
        $this->assertSame('Sprinkler + Hydrant', $site->fire_safety_type);
        $this->assertTrue((bool) $site->fire_safety_available);
        $this->assertSame(2019, $site->construction_year);

        // Aliased contact columns.
        $this->assertSame('Ramesh Iyer', $site->contact_person);
        $this->assertSame('+91 98200 00000', $site->phone);

        // Derived fields.
        $this->assertSame('Sunrise Co-op Housing Society', $site->client_details);
        $this->assertSame('A-112', $site->code);
    }

    public function test_it_links_the_building_and_records_the_conversion(): void
    {
        $building = $this->makeBuilding();

        $result = $this->conversion->convert($building);

        $building->refresh();
        $this->assertSame($result['site']->id, $building->site_id);
        $this->assertNotNull($building->converted_at);
        $this->assertSame('Sunrise Residency', $building->converted_site_name);
        $this->assertTrue($building->isConverted());
    }

    public function test_it_moves_the_buildings_detail_children_to_the_site(): void
    {
        $building = $this->makeBuilding();

        foreach (['building_wings', 'building_contacts', 'fire_systems'] as $table) {
            DB::table($table)->insert([
                'building_id' => $building->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // A child belonging to a different building must not be touched.
        $other = $this->makeBuilding(['name' => 'Other Tower']);
        DB::table('building_wings')->insert([
            'building_id' => $other->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $result = $this->conversion->convert($building);
        $siteId = $result['site']->id;

        foreach (['building_wings', 'building_contacts', 'fire_systems'] as $table) {
            $this->assertSame(1, DB::table($table)->where('building_id', $building->id)->where('site_id', $siteId)->count(), $table);
        }

        $this->assertSame(0, DB::table('building_wings')->where('building_id', $other->id)->whereNotNull('site_id')->count());
        $this->assertSame(1, $result['children_moved']['building_wings']);
    }

    public function test_converting_twice_refreshes_the_same_site(): void
    {
        $building = $this->makeBuilding();

        $first = $this->conversion->convert($building);

        // Survey changes after the first conversion.
        $building->update(['no_of_floors' => 22, 'fire_noc_status' => 'Applied']);
        $second = $this->conversion->convert($building->fresh());

        $this->assertFalse($second['created']);
        $this->assertTrue($second['already_converted']);
        $this->assertSame($first['site']->id, $second['site']->id);
        $this->assertSame(1, Site::count());

        // The refresh carries the newer survey across.
        $this->assertSame(22, $second['site']->no_of_floors);
        $this->assertSame('Applied', $second['site']->fire_noc_status);
    }

    public function test_it_adopts_a_site_that_was_linked_by_hand(): void
    {
        $existing = Site::create([
            'name' => 'Mumbai HQ',
            'address' => 'Old address',
            'city' => 'Mumbai',
            'status' => 'Active',
        ]);

        // Linked by hand before the conversion feature existed.
        $building = $this->makeBuilding(['site_id' => $existing->id]);

        $result = $this->conversion->convert($building);

        $this->assertFalse($result['created']);
        $this->assertFalse($result['already_converted']);
        $this->assertSame($existing->id, $result['site']->id);
        $this->assertSame(1, Site::count());
        $this->assertSame('12 Marine Drive', $result['site']->address);
    }

    public function test_it_does_not_clobber_a_different_site_with_the_same_name(): void
    {
        Site::create(['name' => 'Sunrise Residency', 'status' => 'Active']);

        $building = $this->makeBuilding();
        $result = $this->conversion->convert($building);

        $this->assertSame('Sunrise Residency (2)', $result['site']->name);
        $this->assertSame(2, Site::count());
    }

    public function test_repeated_names_each_get_their_own_site(): void
    {
        $first = $this->conversion->convert($this->makeBuilding());
        $second = $this->conversion->convert($this->makeBuilding());

        $this->assertSame('Sunrise Residency', $first['site']->name);
        $this->assertSame('Sunrise Residency (2)', $second['site']->name);
        $this->assertNotSame($first['site']->id, $second['site']->id);
    }

    public function test_a_refresh_keeps_its_own_name_even_when_the_building_is_renamed(): void
    {
        $building = $this->makeBuilding();
        $first = $this->conversion->convert($building);

        $building->update(['name' => 'Sunrise Residency Phase 2']);
        $second = $this->conversion->convert($building->fresh());

        $this->assertSame($first['site']->id, $second['site']->id);
        $this->assertSame('Sunrise Residency Phase 2', $second['site']->name);
        $this->assertSame(1, Site::count());
    }

    public function test_conversion_works_without_the_child_tables_having_site_id(): void
    {
        // Guards the Schema::hasColumn branch: an un-migrated database should
        // still convert rather than fatal.
        Schema::table('building_wings', function (Blueprint $t) {
            $t->dropForeign(['site_id']);
        });
        Schema::table('building_wings', function (Blueprint $t) {
            $t->dropIndex(['site_id']);
        });
        Schema::table('building_wings', function (Blueprint $t) {
            $t->dropColumn('site_id');
        });

        DB::table('building_wings')->insert([
            'building_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $result = $this->conversion->convert($this->makeBuilding());

        $this->assertTrue($result['created']);
        $this->assertArrayNotHasKey('building_wings', $result['children_moved']);
    }

    public function test_it_mirrors_flat_products_to_the_site(): void
    {
        // The room-product table ships with `site_id` already on it, so mirror
        // it here the way the real migration does rather than via 000002.
        Schema::create('building_flat_products', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('building_id');
            $t->unsignedBigInteger('site_id')->nullable();
            $t->timestamps();
        });

        $building = $this->makeBuilding();

        DB::table('building_flat_products')->insert([
            'building_id' => $building->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $result = $this->conversion->convert($building);
        $siteId = $result['site']->id;

        $this->assertSame(1, $result['children_moved']['building_flat_products']);
        $this->assertSame(
            1,
            DB::table('building_flat_products')->where('building_id', $building->id)->where('site_id', $siteId)->count()
        );
    }
}