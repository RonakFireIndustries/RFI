<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * The product picker of the item vouchers (slice 3): `GET /vouchers/products`
 * is authorised through the voucher's own module, so keying a purchase or
 * sales voucher does not require the products module.
 *
 * As in the other accounting tests, only the migrations this endpoint needs
 * are run — the full chain is MySQL-specific in places — and `products` is
 * shaped by hand.
 */
class VoucherProductPickingTest extends TestCase
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
        '2026_10_17_000007_create_voucher_items_table.php',
        '2026_10_17_000008_add_document_totals_to_vouchers_table.php',
        '2026_10_21_000002_add_bill_level_tax_discount_to_vouchers.php',
        '2026_10_19_000001_add_due_date_to_vouchers.php',
        '2026_10_19_000002_add_credit_days_to_parties.php',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        foreach (self::MIGRATIONS as $file) {
            $migration = require __DIR__ . '/../../database/migrations/' . $file;
            $migration->up();
        }

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->nullable();
            $table->string('hsn_code')->nullable();
            $table->string('dimension')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('unit_id')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Product::create(['name' => 'Widget', 'hsn_code' => '9988', 'status' => 'active']);
        Product::create(['name' => 'Bolt', 'hsn_code' => '7788', 'status' => 'active']);
    }

    private function userWith(array $names): User
    {
        foreach ($names as $name) {
            Permission::findOrCreate($name, 'web');
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $user = User::factory()->create();
        $user->givePermissionTo($names);

        return $user;
    }

    public function test_it_lists_products_for_a_voucher_permission(): void
    {
        $user = $this->userWith(['purchase-vouchers.view']);
        $this->assertFalse($user->can('products.view'));

        $response = $this->actingAs($user)
            ->getJson('/api/v1/vouchers/products?voucher_type=purchase');

        $response->assertOk();
        $response->assertJsonCount(2, 'data');
        // Ordered by name, and only the columns a picking select needs.
        $response->assertJsonPath('data.0.name', 'Bolt');
        $response->assertJsonPath('data.0.hsn_code', '7788');
        $response->assertJsonPath('data.1.name', 'Widget');
        $this->assertSame(
            ['id', 'name', 'sku', 'hsn_code', 'dimension', 'description', 'unit'],
            array_keys($response->json('data.0'))
        );
    }

    public function test_the_listing_is_scoped_to_the_requested_type(): void
    {
        $user = $this->userWith(['sales-vouchers.view']);

        $this->actingAs($user)
            ->getJson('/api/v1/vouchers/products?voucher_type=sales')
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->actingAs($user)
            ->getJson('/api/v1/vouchers/products?voucher_type=purchase')
            ->assertForbidden();
    }

    public function test_the_products_module_alone_is_not_enough(): void
    {
        $user = $this->userWith(['products.view']);

        $this->actingAs($user)
            ->getJson('/api/v1/vouchers/products?voucher_type=purchase')
            ->assertForbidden();
    }

    public function test_it_rejects_non_item_and_unknown_voucher_types(): void
    {
        $user = $this->userWith(['journal.view', 'purchase-vouchers.view']);

        // A voucher type that carries no lines.
        $this->actingAs($user)
            ->getJson('/api/v1/vouchers/products?voucher_type=journal')
            ->assertStatus(422);

        $this->actingAs($user)
            ->getJson('/api/v1/vouchers/products?voucher_type=nope')
            ->assertStatus(422);

        $this->actingAs($user)
            ->getJson('/api/v1/vouchers/products')
            ->assertStatus(422);
    }
}
