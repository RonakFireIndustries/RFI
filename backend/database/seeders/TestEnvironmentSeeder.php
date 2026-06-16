<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;
use App\Models\User;
use App\Models\Employee;
use App\Models\Category;
use App\Models\Product;
use App\Models\Inventory;
use Illuminate\Support\Facades\Hash;

class TestEnvironmentSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
            ['name' => 'Mumbai Warehouse', 'location' => 'Mumbai'],
            ['name' => 'Pune Warehouse', 'location' => 'Pune'],
            ['name' => 'Delhi Hub', 'location' => 'Delhi'],
        ];

        foreach ($branches as $branchData) {
            $branch = Branch::firstOrCreate(['name' => $branchData['name']], $branchData);
            
            $manager = User::firstOrCreate(['email' => strtolower($branchData['location']) . 'manager@example.com'], [
                'name' => $branchData['location'] . ' Manager',
                'password' => Hash::make('password'),
                'branch_id' => $branch->id,
            ]);
            $manager->assignRole('Warehouse Manager');

            Employee::withoutGlobalScopes()->firstOrCreate(['user_id' => $manager->id], [
                'department' => 'Operations',
                'salary' => 80000,
                'joining_date' => now(),
                'branch_id' => $branch->id,
                'designation' => 'Warehouse Manager'
            ]);

            $hr = User::firstOrCreate(['email' => strtolower($branchData['location']) . 'hr@example.com'], [
                'name' => $branchData['location'] . ' HR',
                'password' => Hash::make('password'),
                'branch_id' => $branch->id,
            ]);
            $hr->assignRole('HR');

            Employee::withoutGlobalScopes()->firstOrCreate(['user_id' => $hr->id], [
                'department' => 'HR',
                'salary' => 60000,
                'joining_date' => now(),
                'branch_id' => $branch->id,
                'designation' => 'HR Specialist',
                'reporting_manager_id' => $manager->id
            ]);
        }

        $cat1 = Category::firstOrCreate(['name' => 'Electronics']);
        $cat2 = Category::firstOrCreate(['name' => 'Furniture']);

        $products = [
            ['name' => 'Laptop X1', 'sku' => 'LAP-X1', 'purchase_price' => 1000, 'selling_price' => 1200, 'category_id' => $cat1->id],
            ['name' => 'Desktop Pro', 'sku' => 'DESK-PRO', 'purchase_price' => 600, 'selling_price' => 800, 'category_id' => $cat1->id],
            ['name' => 'Office Chair', 'sku' => 'CHAIR-01', 'purchase_price' => 100, 'selling_price' => 150, 'category_id' => $cat2->id],
        ];

        $allBranches = Branch::all();

        foreach ($products as $pData) {
            $product = Product::firstOrCreate(['sku' => $pData['sku']], $pData);

            foreach ($allBranches as $index => $branch) {
                Inventory::withoutGlobalScopes()->firstOrCreate(
                    ['branch_id' => $branch->id, 'product_id' => $product->id],
                    ['quantity' => ($index + 1) * 10]
                );
            }
        }
    }
}
