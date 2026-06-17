<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeSiteHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'assigned_by_id',
        'previous_site_id',
        'new_site_id',
        'assigned_at',
        'transfer_date',
        'remarks'
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'transfer_date' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_id');
    }

    public function previousSite(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'previous_site_id');
    }

    public function newSite(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'new_site_id');
    }
}
