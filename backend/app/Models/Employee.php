<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'emp_id', 'full_name', 'photo_path', 'interview_date',
        'joining_date', 'probation_end_date', 'dob', 'department_id',
        'designation_id', 'employment_type', 'reporting_manager_id',
        'gender', 'marital_status', 'government_id_type', 'address',
        'contact_number', 'personal_number', 'emergency_contact',
        'qualification', 'employment_bond_status', 'previous_termination_status',
        'legal_proceedings_status', 'resume_path', 'aadhaar_path',
        'pan_path', 'offer_letter_path'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'reporting_manager_id');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(Employee::class, 'reporting_manager_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'documentable_id')->where('documentable_type', self::class);
    }

    public function employeeSites(): HasMany
    {
        return $this->hasMany(EmployeeSite::class);
    }

    public function siteHistories(): HasMany
    {
        return $this->hasMany(EmployeeSiteHistory::class);
    }
}
