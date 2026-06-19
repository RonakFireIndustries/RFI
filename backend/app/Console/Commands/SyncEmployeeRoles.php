<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Console\Command;

class SyncEmployeeRoles extends Command
{
    protected $signature = 'employees:sync-roles';
    protected $description = 'Sync each employee user account role to match their current designation';

    public function handle(): int
    {
        $employees = Employee::with(['designation', 'user.roles'])->whereNotNull('user_id')->get();
        $synced = 0;
        $skipped = 0;

        foreach ($employees as $employee) {
            $designationName = $employee->designation?->name;
            if (!$designationName) {
                $this->warn("Employee #{$employee->id} has no designation, skipping");
                $skipped++;
                continue;
            }

            $user = $employee->user;
            if (!$user) {
                $this->warn("Employee #{$employee->id} has no linked user, skipping");
                $skipped++;
                continue;
            }

            $currentRoles = $user->roles->pluck('name')->toArray();
            if (in_array($designationName, $currentRoles)) {
                $skipped++;
                continue;
            }

            $role = \App\Models\Role::firstOrCreate(['name' => $designationName, 'guard_name' => 'web']);
            $user->roles()->sync([$role->id]);
            $this->info("User #{$user->id} {$user->email}: synced role to '{$designationName}'");
            $synced++;
        }

        $this->info("Done. {$synced} synced, {$skipped} already correct / skipped.");
        return Command::SUCCESS;
    }
}
