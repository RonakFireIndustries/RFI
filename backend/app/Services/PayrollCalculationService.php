<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\PayrollPeriod;
use App\Models\SalaryStructure;
use App\Support\StatutoryDeductions;
use Carbon\Carbon;

/**
 * Attendance-driven payroll.
 *
 * Every working day of the payroll period is visited once and classified as
 * paid time or unpaid time:
 *
 *   - weekly off (Sunday) and gazetted holidays are not working days at all,
 *     so they neither add nor remove pay;
 *   - an approved leave covers the day (paid leave keeps the day paid, LWP /
 *     "unpaid" leave makes it unpaid);
 *   - otherwise the attendance record decides: Present / Late = paid,
 *     Half Day = half paid + half unpaid, Absent = unpaid;
 *   - a working day with no attendance record at all is treated as absent.
 *
 * Pay is then pro-rated: salary components x (paid days / working days).
 * Late marks add a penalty on top (3 lates = half day, 5 lates = one full day,
 * the same rule the attendance module uses).
 */
class PayrollCalculationService
{
    /** Week days that are never working days. */
    protected const WEEKLY_OFF = [Carbon::SUNDAY];

    /** Hours in a working day, used to derive the hourly overtime rate. */
    protected const HOURS_PER_DAY = 8;

    /** Multiplier applied to the hourly rate for overtime. */
    protected const OVERTIME_MULTIPLIER = 1.5;

    /**
     * @return array<string, mixed> attributes for the Payroll model, plus a
     *                               'breakdown' key describing how the number
     *                               was reached (it is not a table column and
     *                               is dropped by mass assignment).
     */
    public function calculate(Employee $employee, PayrollPeriod $period): array
    {
        $salaryStructure = SalaryStructure::where('employee_id', $employee->id)
            ->where('status', 'active')
            ->where('effective_from', '<=', $period->end_date)
            ->where(function ($query) use ($period) {
                $query->whereNull('effective_to')
                      ->orWhere('effective_to', '>=', $period->start_date);
            })
            ->latest('effective_from')
            ->first();

        if (!$salaryStructure) {
            throw new \Exception("No active salary structure found for this employee in the given period.");
        }

        $summary = $this->summarizeAttendance($employee, $period);

        $workingDays = (float) $summary['total_working_days'];
        $paidDays = (float) $summary['paid_days'];

        // Share of the period that was actually paid time. A month with full
        // attendance pays 1.0, a half day drags it to 0.98, an unpaid leave
        // week to ~0.75, and so on.
        $prorataFactor = $workingDays > 0 ? $paidDays / $workingDays : 0.0;

        $dailyRate = $workingDays > 0 ? (float) $salaryStructure->basic_salary / $workingDays : 0.0;
        $hourlyRate = $workingDays > 0 ? $dailyRate / self::HOURS_PER_DAY : 0.0;

        // 1. Earnings (pro-rated)
        $basicSalary = (float) $salaryStructure->basic_salary * $prorataFactor;
        $hra = (float) $salaryStructure->hra * $prorataFactor;
        $conveyance = (float) $salaryStructure->conveyance * $prorataFactor;
        $medicalAllowance = (float) $salaryStructure->medical_allowance * $prorataFactor;
        $specialAllowance = (float) $salaryStructure->special_allowance * $prorataFactor;
        $siteAllowance = (float) $salaryStructure->site_allowance * $prorataFactor;
        $travelAllowance = (float) $salaryStructure->travel_allowance * $prorataFactor;
        $foodAllowance = (float) $salaryStructure->food_allowance * $prorataFactor;
        $otherEarnings = (float) $salaryStructure->other_earnings * $prorataFactor;

        // Overtime is paid on top of the pro-rated salary.
        $overtimePay = (float) $summary['overtime_hours'] * $hourlyRate * self::OVERTIME_MULTIPLIER;
        $bonuses = 0;

        $grossSalary = $basicSalary + $hra + $conveyance + $medicalAllowance
            + $specialAllowance + $siteAllowance + $travelAllowance
            + $foodAllowance + $otherEarnings + $overtimePay + $bonuses;

        // 2. Deductions.
        //    PF and ESIC are either derived from the statutory rule (12% of
        //    Basic capped at the EPF wage ceiling, 0.75% of gross while the
        //    employee stays under the ESIC ceiling) or taken from the manually
        //    configured amounts when auto-calc is switched off for this
        //    structure. Either way they are charged in full - attendance has
        //    already reduced the earnings above.
        if ($salaryStructure->auto_statutory) {
            $statutory = StatutoryDeductions::forStructure($salaryStructure);
            $pf = $statutory['pf'];
            $esic = $statutory['esic'];
        } else {
            $pf = (float) $salaryStructure->pf_deduction;
            $esic = (float) $salaryStructure->esic_deduction;
        }
        $pt = (float) $salaryStructure->professional_tax;
        $tds = (float) $salaryStructure->tds;
        $otherDeductions = (float) $salaryStructure->other_deductions;

        $latePenalty = round((float) $summary['late_deduction_days'] * $dailyRate, 2);
        $lossOfPay = round((float) $summary['unpaid_days'] * $dailyRate, 2);
        $salaryAdvance = 0; // Fetch from the salary advance module if one exists

        $totalDeductions = $pf + $esic + $pt + $tds + $otherDeductions + $latePenalty + $salaryAdvance;

        // 3. Net salary (never negative: a zero-attendance month must not bill
        //    the employee for their own statutory deductions).
        $netSalary = max(0, $grossSalary - $totalDeductions);

        return [
            'employee_id' => $employee->id,
            'payroll_period_id' => $period->id,
            'month' => $period->month,
            'year' => $period->year,

            // Earnings
            'basic_salary' => round($basicSalary, 2),
            'hra' => round($hra, 2),
            'conveyance' => round($conveyance, 2),
            'medical_allowance' => round($medicalAllowance, 2),
            'special_allowance' => round($specialAllowance, 2),
            'site_allowance' => round($siteAllowance, 2),
            'travel_allowance' => round($travelAllowance, 2),
            'food_allowance' => round($foodAllowance, 2),
            'other_allowance' => round($otherEarnings, 2),
            'overtime_pay' => round($overtimePay, 2),
            'bonuses' => round($bonuses, 2),

            // Deductions
            'pf' => round($pf, 2),
            'esic' => round($esic, 2),
            'pt' => round($pt, 2),
            'tds' => round($tds, 2),
            'other_deductions' => round($otherDeductions, 2),
            'late_penalty' => $latePenalty,
            'salary_advance' => round($salaryAdvance, 2),
            'loss_of_pay' => $lossOfPay,

            // Summaries
            'net_salary' => round($netSalary, 2),
            'present_days' => round((float) $summary['present_days'], 2),
            'absent_days' => round((float) $summary['absent_days'], 2),
            'paid_leaves' => round((float) $summary['paid_leaves'], 2),
            'unpaid_leaves' => round((float) $summary['unpaid_leaves'], 2),
            'working_days' => round($workingDays, 2),

            // Not columns: kept here for the caller / payslip breakdown.
            'late_marks' => $summary['late_marks'],
            'late_deduction_days' => $summary['late_deduction_days'],
            'overtime_hours' => $summary['overtime_hours'],
            'paid_days' => round($paidDays, 2),
            'unpaid_days' => round((float) $summary['unpaid_days'], 2),
            'prorata_factor' => round($prorataFactor, 4),
            'daily_rate' => round($dailyRate, 2),
            'breakdown' => $summary,

            'status' => 'Draft',
        ];
    }

    /**
     * Classify every day of the period for one employee.
     *
     * @return array<string, float|int>
     */
    public function summarizeAttendance(Employee $employee, PayrollPeriod $period): array
    {
        $start = Carbon::parse($period->start_date)->startOfDay();
        $end = Carbon::parse($period->end_date)->endOfDay();
        $startKey = $start->toDateString();
        $endKey = $end->toDateString();

        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$startKey, $endKey])
            ->get()
            ->keyBy(fn (Attendance $attendance) => Carbon::parse($attendance->date)->toDateString());

        $holidays = Holiday::whereBetween('date', [$startKey, $endKey])
            ->active()
            ->pluck('date')
            ->mapWithKeys(fn ($date) => [Carbon::parse($date)->toDateString() => true])
            ->all();

        $leaveDays = $this->approvedLeaveDays($employee, $startKey, $endKey);

        $summary = [
            'total_calendar_days' => 0,
            'weekly_off_days' => 0,
            'holiday_days' => 0,
            'total_working_days' => 0,
            'present_days' => 0.0,
            'absent_days' => 0.0,
            'half_days' => 0.0,
            'late_marks' => 0,
            'paid_leaves' => 0.0,
            'unpaid_leaves' => 0.0,
            'paid_days' => 0.0,
            'unpaid_days' => 0.0,
            'late_deduction_days' => 0.0,
            'overtime_hours' => 0.0,
            'days_without_attendance' => 0,
        ];

        $cursor = $start->copy();

        while ($cursor <= $end) {
            $key = $cursor->toDateString();
            $summary['total_calendar_days']++;

            if (in_array($cursor->dayOfWeek, self::WEEKLY_OFF, true)) {
                $summary['weekly_off_days']++;
                $cursor->addDay();
                continue;
            }

            if (isset($holidays[$key])) {
                $summary['holiday_days']++;
                $cursor->addDay();
                continue;
            }

            $summary['total_working_days']++;

            $record = $attendances->get($key);
            $status = strtoupper(trim((string) ($record->status ?? '')));

            if ($record) {
                $summary['overtime_hours'] += (float) $record->overtime_hours;
                if ($record->is_late || $status === 'LATE') {
                    $summary['late_marks']++;
                }
            }

            // Portion of the day covered by an approved leave, if any.
            $paid = 0.0;
            $unpaid = 0.0;
            $remaining = 1.0;

            if (isset($leaveDays[$key])) {
                $leave = $leaveDays[$key];
                if ($leave['kind'] === 'unpaid') {
                    $unpaid += $leave['days'];
                    $summary['unpaid_leaves'] += $leave['days'];
                } else {
                    $paid += $leave['days'];
                    $summary['paid_leaves'] += $leave['days'];
                }
                $remaining -= $leave['days'];
            }

            if ($remaining > 0) {
                if (!$record) {
                    // Working day, no attendance and no approved leave.
                    $unpaid += $remaining;
                    $summary['absent_days'] += $remaining;
                    $summary['days_without_attendance']++;
                } elseif ($status === 'ABSENT') {
                    $unpaid += $remaining;
                    $summary['absent_days'] += $remaining;
                } elseif (str_contains($status, 'HALF')) {
                    $paid += $remaining / 2;
                    $unpaid += $remaining / 2;
                    $summary['half_days'] += 1;
                    $summary['present_days'] += $remaining / 2;
                    $summary['absent_days'] += $remaining / 2;
                } elseif ($status === 'ON LEAVE') {
                    // Attendance was written by an approved leave that we could
                    // not match (e.g. deleted request): keep the day paid.
                    $paid += $remaining;
                    $summary['paid_leaves'] += $remaining;
                } else {
                    // Present / Late / anything else counts as a worked day.
                    $paid += $remaining;
                    $summary['present_days'] += $remaining;
                }
            }

            $summary['paid_days'] += $paid;
            $summary['unpaid_days'] += $unpaid;

            $cursor->addDay();
        }

        // Late penalty rule shared with Attendance::scopeGetLateMarksDeduction:
        // every 3rd late mark costs half a day, every 5th a full day.
        $summary['late_deduction_days'] = (intdiv($summary['late_marks'], 3) * 0.5)
            + (intdiv($summary['late_marks'], 5) * 1.0);

        $summary['total_working_days'] = (float) $summary['total_working_days'];
        $summary['overtime_hours'] = round($summary['overtime_hours'], 2);
        $summary['late_deduction_days'] = round($summary['late_deduction_days'], 2);

        return $summary;
    }

    /**
     * Map of "Y-m-d" => ['kind' => paid|unpaid, 'days' => 1|0.5] for every day
     * covered by an approved leave inside the period.
     *
     * @return array<string, array{kind: string, days: float}>
     */
    protected function approvedLeaveDays(Employee $employee, string $startKey, string $endKey): array
    {
        $days = [];

        $leaves = Leave::with('leaveType')
            ->where('employee_id', $employee->id)
            ->where('status', 'Approved')
            ->where('start_date', '<=', $endKey)
            ->where('end_date', '>=', $startKey)
            ->get();

        foreach ($leaves as $leave) {
            $kind = $this->isUnpaidLeave($leave->leaveType) ? 'unpaid' : 'paid';
            $weight = $leave->is_half_day ? 0.5 : 1.0;

            $cursor = Carbon::parse($leave->start_date)->startOfDay();
            $last = Carbon::parse($leave->end_date)->startOfDay();

            while ($cursor <= $last) {
                $key = $cursor->toDateString();
                if ($key >= $startKey && $key <= $endKey) {
                    $existing = $days[$key] ?? null;
                    if (!$existing || $weight > $existing['days']) {
                        $days[$key] = ['kind' => $kind, 'days' => $weight];
                    }
                }
                $cursor->addDay();
            }
        }

        return $days;
    }

    /**
     * Leave types that reduce pay. There is no `is_paid` column, so the type
     * name/code decides: LWP, "Unpaid" and "Loss of Pay" are unpaid.
     */
    protected function isUnpaidLeave(?LeaveType $leaveType): bool
    {
        if (!$leaveType) {
            return false;
        }

        $label = strtoupper(trim("{$leaveType->name} {$leaveType->code}"));

        return str_contains($label, 'LWP')
            || str_contains($label, 'UNPAID')
            || str_contains($label, 'LOSS OF PAY');
    }
}
