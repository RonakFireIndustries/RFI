<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $departmentMap = [];

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('designations')->truncate();
        DB::table('departments')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $departments = [
            ['name' => 'Administration', 'description' => 'Core management and administrative operations'],
            ['name' => 'Production', 'description' => 'Manufacturing, fabrication, and assembly operations'],
            ['name' => 'Accounts', 'description' => 'Financial management, bookkeeping, and accounting'],
            ['name' => 'IT', 'description' => 'Information technology and system administration'],
            ['name' => 'Designer', 'description' => 'Design, drafting, and creative development'],
            ['name' => 'Inventory & Warehouse', 'description' => 'Stock management and material tracking'],
            ['name' => 'Sales', 'description' => 'Sales, client acquisition, and business development'],
            ['name' => 'Other', 'description' => 'For employees whose departments are not fixed'],
        ];

        $now = now();
        foreach ($departments as $i => $dept) {
            $id = DB::table('departments')->insertGetId([
                'name' => $dept['name'],
                'description' => $dept['description'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $departmentMap[$dept['name']] = $id;
        }

        $designations = [
            ['name' => 'Admin', 'department' => 'Administration'],
            ['name' => 'Manager', 'department' => 'Administration'],
            ['name' => 'Designer', 'department' => 'Designer'],
            ['name' => 'Accountant', 'department' => 'Accounts'],
            ['name' => 'Developer', 'department' => 'IT'],
            ['name' => 'Fitter', 'department' => 'Production'],
            ['name' => 'Welder', 'department' => 'Production'],
            ['name' => 'Electrician', 'department' => 'Production'],
            ['name' => 'Helper', 'department' => 'Production'],
            ['name' => 'Store Manager', 'department' => 'Inventory & Warehouse'],
        ];

        foreach ($designations as $des) {
            DB::table('designations')->insert([
                'name' => $des['name'],
                'department_id' => $departmentMap[$des['department']],
                'description' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
    }
};
