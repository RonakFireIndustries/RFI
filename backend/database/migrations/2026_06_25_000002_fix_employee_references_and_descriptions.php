<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ─── Map old department IDs to new department IDs ───
        // Old: 1=Administration, 2=Production&Workshop, 3=Finance&Accounts,
        //      4=Engineering&Design, 5=Inventory&Warehouse, 7=Information Technology, 8=Sales&Marketing
        // New: 1=Administration, 2=Production, 3=Accounts, 4=IT,
        //      5=Designer, 6=Inventory&Warehouse, 7=Sales, 8=Other
        $deptMap = [
            1 => 1,  // Administration → Administration
            2 => 2,  // Production & Workshop → Production
            3 => 3,  // Finance & Accounts → Accounts
            4 => 5,  // Engineering & Design → Designer
            5 => 6,  // Inventory & Warehouse → Inventory & Warehouse
            7 => 4,  // Information Technology → IT
            8 => 7,  // Sales & Marketing → Sales
        ];

        foreach ($deptMap as $oldId => $newId) {
            DB::table('employees')
                ->where('department_id', $oldId)
                ->update(['department_id' => $newId]);
        }

        // Employees with old department_id=6 (Human Resources, removed) → default to Administration
        DB::table('employees')
            ->where('department_id', 6)
            ->update(['department_id' => 1]);

        // ─── Map old designation IDs to new designation IDs ───
        // Old: 1=SuperAdmin, 4=GeneralManager, 6=WorkshopSupervisor, 7=Fitter, 8=Welder,
        //      9=Electrician, 10=Helper, 11=FinanceManager, 12=Accountant, 13=DesignManager, 15=StoreManager
        // New: 1=Admin, 2=Manager, 3=Designer, 4=Accountant, 5=Developer,
        //      6=Fitter, 7=Welder, 8=Electrician, 9=Helper, 10=StoreManager
        $desigMap = [
            1  => 1,  // Super Admin → Admin
            4  => 2,  // General Manager → Manager
            6  => 2,  // Workshop Supervisor → Manager
            7  => 6,  // Fitter → Fitter
            8  => 7,  // Welder → Welder
            9  => 8,  // Electrician → Electrician
            10 => 9,  // Helper → Helper
            11 => 4,  // Finance Manager → Accountant
            12 => 4,  // Accountant → Accountant
            13 => 3,  // Design Manager → Designer
            15 => 10, // Store Manager → Store Manager
        ];

        foreach ($desigMap as $oldId => $newId) {
            DB::table('employees')
                ->where('designation_id', $oldId)
                ->update(['designation_id' => $newId]);
        }

        // ─── Add descriptions to designations ───
        DB::table('designations')->where('name', 'Admin')->update([
            'description' => 'General administrative management and office operations',
        ]);
        DB::table('designations')->where('name', 'Manager')->update([
            'description' => 'Departmental oversight, team management, and operational supervision',
        ]);
        DB::table('designations')->where('name', 'Designer')->update([
            'description' => 'Design, drafting, and creative development',
        ]);
        DB::table('designations')->where('name', 'Accountant')->update([
            'description' => 'Financial bookkeeping, invoicing, and ledger management',
        ]);
        DB::table('designations')->where('name', 'Developer')->update([
            'description' => 'Software development, IT support, and system administration',
        ]);
        DB::table('designations')->where('name', 'Fitter')->update([
            'description' => 'Mechanical fitting, assembly, and structural alignment',
        ]);
        DB::table('designations')->where('name', 'Welder')->update([
            'description' => 'Welding, fabrication, and metal joining operations',
        ]);
        DB::table('designations')->where('name', 'Electrician')->update([
            'description' => 'Electrical installation, maintenance, and wiring',
        ]);
        DB::table('designations')->where('name', 'Helper')->update([
            'description' => 'General workshop assistance and manual labor support',
        ]);
        DB::table('designations')->where('name', 'Store Manager')->update([
            'description' => 'Inventory control, stock management, and logistics',
        ]);
    }

    public function down(): void
    {
    }
};
