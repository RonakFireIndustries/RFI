<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AutoCloseForgottenAttendances extends Command
{
    protected $signature = 'attendance:auto-close-forgotten {--dry-run : Report what would change without writing}';

    protected $description = 'Close attendance records where an employee checked in but never checked out, using the shift end time once a grace period has passed.';

    public function handle(): int
    {
        $graceHours = (int) config('attendance.auto_checkout_grace_hours', 2);
        $lookbackDays = (int) config('attendance.auto_checkout_lookback_days', 7);
        $dryRun = (bool) $this->option('dry-run');

        $now = Carbon::now();
        $today = $now->copy()->startOfDay();

        $candidates = Attendance::with(['shift', 'employee.currentShift.shift'])
            ->whereNotNull('check_in')
            ->whereNull('check_out')
            ->where('date', '<=', $today->toDateString())
            ->where('date', '>=', $today->copy()->subDays($lookbackDays)->toDateString())
            ->get();

        if ($candidates->isEmpty()) {
            $this->info('No forgotten check-outs found.');
            return Command::SUCCESS;
        }

        $closed = 0;
        $skipped = 0;

        foreach ($candidates as $attendance) {
            $shift = $attendance->shift ?: $attendance->employee?->currentShift?->shift;

            $shiftEnd = $shift ? $this->shiftEndDateTime($attendance, $shift) : null;

            if (!$shiftEnd) {
                $skipped++;
                continue;
            }

            // Only close once the grace period after the scheduled shift end has passed.
            if ($now->lt($shiftEnd->copy()->addHours($graceHours))) {
                continue;
            }

            // Never close a record before it was even checked in.
            if ($attendance->check_in && $attendance->check_in->gt($shiftEnd)) {
                $skipped++;
                continue;
            }

            $this->line(sprintf(
                '  %s  record #%d  check-in %s -> close at %s',
                $attendance->date?->format('Y-m-d') ?? '',
                $attendance->id,
                $attendance->check_in?->format('H:i') ?? '',
                $shiftEnd->format('Y-m-d H:i')
            ));

            if (!$dryRun) {
                $attendance->check_out = $shiftEnd;
                $attendance->is_auto_checkout = true;
                $attendance->remarks = $attendance->remarks
                    ? $attendance->remarks . ' | ' . Attendance::AUTO_CHECKOUT_REMARK
                    : Attendance::AUTO_CHECKOUT_REMARK;
                $attendance->save();
            }

            $closed++;
        }

        $this->info(($dryRun ? '[DRY RUN] ' : '') . "Auto-closed {$closed} attendance record(s).");
        if ($skipped > 0) {
            $this->warn("Skipped {$skipped} record(s) with no usable shift end time.");
        }

        return Command::SUCCESS;
    }

    /**
     * The shift's end as a full date-time on the attendance's date. Shifts whose
     * end time is not after their start time cross midnight into the next day.
     */
    protected function shiftEndDateTime(Attendance $attendance, Shift $shift): ?Carbon
    {
        if (!$shift->end_time) {
            return null;
        }

        $date = $attendance->date instanceof \DateTimeInterface
            ? $attendance->date->format('Y-m-d')
            : (string) $attendance->date;

        if ($date === '') {
            return null;
        }

        $endAt = Carbon::parse($date . ' ' . $shift->end_time);

        if ($shift->start_time && $shift->end_time <= $shift->start_time) {
            $endAt->addDay();
        }

        return $endAt;
    }
}
