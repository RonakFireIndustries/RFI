<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalaryStructure extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'basic_salary',
        'hra',
        'conveyance',
        'medical_allowance',
        'special_allowance',
        'site_allowance',
        'travel_allowance',
        'food_allowance',
        'other_earnings',
        'pf_deduction',
        'esic_deduction',
        'professional_tax',
        'tds',
        'other_deductions',
        'effective_from',
        'effective_to',
        'status',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
