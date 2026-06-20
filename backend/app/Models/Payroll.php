<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    protected $fillable = [
        'employee_id',
        'payroll_period_id',
        'month',
        'year',
        'basic_salary',
        'hra',
        'conveyance',
        'medical_allowance',
        'special_allowance',
        'site_allowance',
        'travel_allowance',
        'food_allowance',
        'other_allowance',
        'pf',
        'esic',
        'pt',
        'tds',
        'other_deductions',
        'salary_advance',
        'loss_of_pay',
        'late_penalty',
        'bonuses',
        'overtime_pay',
        'net_salary',
        'present_days',
        'absent_days',
        'paid_leaves',
        'unpaid_leaves',
        'working_days',
        'status',
        'processed_at',
    ];

    public function payrollPeriod(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    public function payslip()
    {
        return $this->hasOne(Payslip::class);
    }

    protected $dates = ['processed_at'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
