<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeShiftHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'assigned_by_id',
        'previous_shift_id',
        'new_shift_id',
        'assigned_at',
        'effective_date',
        'remarks'
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'effective_date' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_id');
    }

    public function previousShift(): BelongsTo
    {
        return $this->belongsTo(Shift::class, 'previous_shift_id');
    }

    public function newShift(): BelongsTo
    {
        return $this->belongsTo(Shift::class, 'new_shift_id');
    }
}
