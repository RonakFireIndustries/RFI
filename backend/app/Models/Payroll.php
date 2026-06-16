<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToBranch;

class Payroll extends Model
{
    use BelongsToBranch;

    protected $fillable = [
        'employee_id',
        'branch_id',
        'salary',
        'basic_pay',
        'allowances',
        'deductions',
        'net_pay',
        'month',
        'year',
        'status',
        'processed_at',
    ];

    protected $dates = ['processed_at'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
