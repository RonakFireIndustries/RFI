<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Services\LeaveBalanceService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class InitializeLeaveBalances extends Command
{
    protected $signature = 'leave:initialize-balances
                            {--year= : Year to initialise (defaults to the current year)}
                            {--employee= : Only initialise this single employee id}
                            {--dry-run : Report what would be created without writing}';

    protected $description = 'Create missing employee leave balances so the Leave Balance card and page show real numbers instead of zero.';

    public function handle(LeaveBalanceService $service): int
    {
        $year = (int) ($this->option('year') ?: Carbon::now()->year);
        $dryRun = (bool) $this->option('dry-run');

        $query = Employee::query()->orderBy('id');

        if ($employeeId = $this->option('employee')) {
            $query->whereKey($employeeId);
        }

        $employees = $query->get();

        if ($employees->isEmpty()) {
            $this->warn('No matching employees found.');

            return Command::SUCCESS;
        }

        $before = LeaveBalance::where('year', $year)->count();

        if ($dryRun) {
            $this->info("Dry run: {$employees->count()} employee(s) would be processed for {$year}.");
            $this->line("Existing {$year} balance rows: {$before}.");

            return Command::SUCCESS;
        }

        $failed = 0;

        $bar = $this->output->createProgressBar($employees->count());
        $bar->start();

        foreach ($employees as $employee) {
            try {
                $service->initializeBalances($employee, $year);
            } catch (\Throwable $e) {
                $failed++;
                $this->newLine();
                $this->error("Employee #{$employee->id} ({$employee->full_name}): {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $after = LeaveBalance::where('year', $year)->count();

        $this->info("Processed {$employees->count()} employee(s) for {$year}.");
        $this->line("Balance rows: {$before} -> {$after} (+" . ($after - $before) . ' created)');

        if ($failed > 0) {
            $this->warn("{$failed} employee(s) failed - see the errors above.");
        }

        if ($after === $before) {
            $this->comment('Nothing to create: every employee already has this year\'s balances, or no Active leave types apply.');
        }

        return Command::SUCCESS;
    }
}
