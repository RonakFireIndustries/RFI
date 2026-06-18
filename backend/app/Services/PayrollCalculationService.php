<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\SalaryStructure;
use App\Models\Attendance;
use App\Models\Leave;
use Carbon\Carbon;

class PayrollCalculationService
{
    public function calculate(Employee $employee, PayrollPeriod $period)
    {
        // 1. Get active Salary Structure
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

        // 2. Calculate Attendances
        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$period->start_date, $period->end_date])
            ->get();

        $presentDays = $attendances->where('status', 'Present')->count();
        $absentDays = $attendances->where('status', 'Absent')->count();
        $lateDays = $attendances->where('status', 'Late')->count();
        $overtimeHours = $attendances->sum('overtime_hours');

        // Present days include Late days for calculation purpose unless specified otherwise
        $totalPresent = $presentDays + $lateDays;

        // 3. Calculate Leaves
        $leaves = Leave::where('employee_id', $employee->id)
            ->where('status', 'Approved')
            ->where(function ($query) use ($period) {
                $query->whereBetween('start_date', [$period->start_date, $period->end_date])
                      ->orWhereBetween('end_date', [$period->start_date, $period->end_date]);
            })
            ->get();

        $paidLeaves = 0;
        $unpaidLeaves = 0;

        foreach ($leaves as $leave) {
            // Count overlapping days
            $leaveStart = Carbon::parse($leave->start_date)->max($period->start_date);
            $leaveEnd = Carbon::parse($leave->end_date)->min($period->end_date);
            $days = $leaveStart->diffInDays($leaveEnd) + 1;

            // Simplified: Assuming a type mapping or property determines paid/unpaid
            // For now, if leave_type is LWP (Loss of Pay) it's unpaid.
            if (optional($leave->leaveType)->name === 'LWP' || optional($leave->leaveType)->is_paid === false) {
                $unpaidLeaves += $days;
            } else {
                $paidLeaves += $days;
            }
        }

        // 4. Calculate Total Payable Days
        $totalDaysInPeriod = Carbon::parse($period->start_date)->diffInDays(Carbon::parse($period->end_date)) + 1;
        
        // Simplified Working Days formula: Total Days - Unpaid Leaves - Absent Days
        $payableDays = $totalDaysInPeriod - $unpaidLeaves - $absentDays;
        
        // Safety bounds
        $payableDays = max(0, min($payableDays, $totalDaysInPeriod));

        // Pro-rata Factor
        $prorataFactor = $totalDaysInPeriod > 0 ? ($payableDays / $totalDaysInPeriod) : 0;

        // 5. Calculate Earnings
        $basicSalary = $salaryStructure->basic_salary * $prorataFactor;
        $hra = $salaryStructure->hra * $prorataFactor;
        $conveyance = $salaryStructure->conveyance * $prorataFactor;
        $medicalAllowance = $salaryStructure->medical_allowance * $prorataFactor;
        $specialAllowance = $salaryStructure->special_allowance * $prorataFactor;
        
        // Site Allowance could be fixed from structure or calculated via EmployeeSite. Using structure for now.
        $siteAllowance = $salaryStructure->site_allowance * $prorataFactor;
        
        $travelAllowance = $salaryStructure->travel_allowance * $prorataFactor;
        $foodAllowance = $salaryStructure->food_allowance * $prorataFactor;
        $otherEarnings = $salaryStructure->other_earnings * $prorataFactor;

        // Overtime Calculation (Assuming Basic Salary / 30 / 8 is hourly rate)
        $hourlyRate = $totalDaysInPeriod > 0 ? ($salaryStructure->basic_salary / $totalDaysInPeriod / 8) : 0;
        $overtimePay = $overtimeHours * $hourlyRate * 1.5; // 1.5x standard OT rate

        $bonuses = 0; // Fixed zero for now, could be dynamic

        $grossSalary = $basicSalary + $hra + $conveyance + $medicalAllowance + 
                       $specialAllowance + $siteAllowance + $travelAllowance + 
                       $foodAllowance + $otherEarnings + $overtimePay + $bonuses;

        // 6. Calculate Deductions
        $pf = $salaryStructure->pf_deduction;
        $esic = $salaryStructure->esic_deduction;
        $pt = $salaryStructure->professional_tax;
        $tds = $salaryStructure->tds;
        $otherDeductions = $salaryStructure->other_deductions;

        // Penalty for late days (e.g., 3 late days = 1 day deduct). Simple logic:
        $latePenaltyDays = floor($lateDays / 3);
        $latePenalty = $latePenaltyDays * ($totalDaysInPeriod > 0 ? ($salaryStructure->basic_salary / $totalDaysInPeriod) : 0);

        $lossOfPay = ($unpaidLeaves + $absentDays) * ($totalDaysInPeriod > 0 ? ($salaryStructure->basic_salary / $totalDaysInPeriod) : 0);
        $salaryAdvance = 0; // Fetch from SalaryAdvance module if exists

        $totalDeductions = $pf + $esic + $pt + $tds + $otherDeductions + $latePenalty + $salaryAdvance;

        // 7. Net Salary
        $netSalary = $grossSalary - $totalDeductions;

        return [
            'employee_id' => $employee->id,
            'branch_id' => $employee->user->branch_id,
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
            'late_penalty' => round($latePenalty, 2),
            'salary_advance' => round($salaryAdvance, 2),
            'loss_of_pay' => round($lossOfPay, 2),
            
            // Summaries
            'net_salary' => round($netSalary, 2),
            'present_days' => $totalPresent,
            'absent_days' => $absentDays,
            'paid_leaves' => $paidLeaves,
            'unpaid_leaves' => $unpaidLeaves,
            'working_days' => $payableDays,
            
            'status' => 'Draft',
        ];
    }
}
