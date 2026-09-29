<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Services\EmployeeService;
use Illuminate\Console\Command;
use Spatie\Permission\PermissionRegistrar;

class BackfillEmployeeAccounts extends Command
{
    protected $signature = 'employees:backfill-accounts
                            {--dry-run : List the employees that would get an account without writing anything}
                            {--id=* : Restrict the backfill to specific employee ids}';
    protected $description = 'Create the missing login account (and email) for employees that were added without one.';

    public function handle(EmployeeService $employeeService): int
    {
        // whereDoesntHave covers both a null user_id and a user_id pointing at a
        // deleted user row, which is why the Email column renders blank.
        $query = Employee::query()->whereDoesntHave('user');

        $ids = array_filter($this->option('id'));
        if ($ids) {
            $query->whereIn('id', $ids);
        }

        $employees = $query->orderBy('id')->get();

        if ($employees->isEmpty()) {
            $this->info('Every employee already has a linked user account. Nothing to do.');
            return Command::SUCCESS;
        }

        $this->info("Employees without a linked user account: {$employees->count()}");

        $rows = [];
        $created = 0;

        foreach ($employees as $employee) {
            $label = $employee->emp_id ?: '-';

            if ($this->option('dry-run')) {
                $rows[] = [$employee->id, $label, $employee->full_name, '(dry run)'];
                continue;
            }

            $credentials = $employeeService->createAccountFor($employee);

            if ($credentials) {
                $created++;
                $rows[] = [
                    $employee->id,
                    $label,
                    $employee->full_name,
                    $credentials['email'],
                    $credentials['temp_password'],
                ];
            }
        }

        $this->table(
            ['ID', 'Emp ID', 'Name', 'Email', 'Temp Password'],
            $rows
        );

        if ($this->option('dry-run')) {
            $this->info('Dry run. Nothing was written. Re-run without --dry-run to apply.');
            return Command::SUCCESS;
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->info("Done. {$created} account(s) created. Hand the temporary passwords over securely, then have each user change it on first login.");
        return Command::SUCCESS;
    }
}
