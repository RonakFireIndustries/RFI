<?php

namespace App\Console\Commands;

use App\Models\Role;
use Illuminate\Console\Command;

class FixEmptyRolePermissions extends Command
{
    protected $signature = 'roles:fix-permissions';
    protected $description = 'Assign base employee permissions to any roles that have zero permissions';

    private array $basePermissions = [
        'view dashboard',
        'attendance.checkin', 'attendance.checkout',
        'attendance.view',
        'daily-report.create', 'daily-report.view', 'daily-report.submit',
        'leave.view', 'leave.create', 'leave.cancel', 'leave.balance.view',
        'view_payroll',
    ];

    public function handle(): int
    {
        $roles = Role::withCount('permissions')->having('permissions_count', '=', 0)->get();

        if ($roles->isEmpty()) {
            $this->info('All roles already have permissions assigned.');
            return Command::SUCCESS;
        }

        foreach ($roles as $role) {
            $role->syncPermissions($this->basePermissions);
            $this->info("Role '{$role->name}' (ID: {$role->id}): assigned " . count($this->basePermissions) . " base permissions.");
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $this->info('Permission cache cleared. Done.');
        return Command::SUCCESS;
    }
}
