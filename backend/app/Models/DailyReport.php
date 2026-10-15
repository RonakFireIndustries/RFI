<?php

namespace App\Models;

use App\Support\Access;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 
        'site_id', 
        'date', 
        'work_description',
        'tasks_completed',
        'hours_worked',
        'issues_faced',
        'materials_used',
        'equipment_used',
        'status',
        'submitted_at',
        'approved_at',
        'approved_by',
        'supervisor_remarks',
    ];

    protected $casts = [
        'date' => 'date',
        'hours_worked' => 'decimal:2',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(DailyReportHistory::class);
    }

    /**
     * Whether the given user may approve / reject / rework this report.
     * Allowed for: super admins, users with an explicit review permission,
     * or the reporting manager of the employee who filed the report.
     * The submitter can never review their own report.
     */
    public function canBeReviewedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        if (Access::isSuperAdmin($user)) {
            return true;
        }

        if ($user->hasAnyPermission(['daily-reports.approve', 'daily-reports.reject'])) {
            return true;
        }

        $reviewer = $user->employee;

        return $this->employee_id !== null
            && $reviewer !== null
            && $this->employee_id !== $reviewer->id
            && $this->employee?->reporting_manager_id === $reviewer->id;
    }

    /**
     * Whether the given user may edit this report.
     *
     * Allowed for: super admins and users with an explicit update permission
     * (they manage reports), or the employee who filed it while it is still a
     * Draft or has been sent back as "Rework Required" — so a worker can fix
     * their own report and resubmit it.
     */
    public function isEditableBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        if (Access::isSuperAdmin($user) || $user->hasAnyPermission(['daily-reports.update'])) {
            return true;
        }

        $owner = $user->employee;

        return $owner !== null
            && $this->employee_id !== null
            && (int) $this->employee_id === (int) $owner->id
            && in_array($this->status, ['Draft', 'Rework Required'], true);
    }
}
