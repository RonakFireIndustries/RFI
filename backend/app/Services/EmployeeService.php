<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class EmployeeService
{
    public function getEmployees(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Employee::with(['department', 'designation', 'manager', 'user']);

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('full_name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('emp_id', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (!empty($filters['designation_id'])) {
            $query->where('designation_id', $filters['designation_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('employment_type', $filters['status']);
        }

        if (!empty($filters['manager_id'])) {
            $query->where('reporting_manager_id', $filters['manager_id']);
        }

        return $query->paginate($perPage);
    }

    private function processUploads(array &$data): void
    {
        $fileFields = [
            'photo' => 'photo_path',
            'resume' => 'resume_path',
            'aadhaar' => 'aadhaar_path',
            'pan' => 'pan_path',
            'offer_letter' => 'offer_letter_path',
        ];

        foreach ($fileFields as $input => $dbColumn) {
            if (isset($data[$input]) && $data[$input] instanceof \Illuminate\Http\UploadedFile) {
                $path = $data[$input]->store('employees', 'public');
                $data[$dbColumn] = $path;
                unset($data[$input]);
            }
        }
    }

    public function createEmployee(array $data): Employee
    {
        $this->processUploads($data);

        return DB::transaction(function () use ($data) {
            $employee = Employee::create($data);

            // Phase 5: Employee User Linking (if requested auto creation)
            if (isset($data['create_user_account']) && $data['create_user_account']) {
                $tempPassword = \Illuminate\Support\Str::random(16);
                $user = User::create([
                    'name' => $data['full_name'],
                    'email' => strtolower(str_replace(' ', '.', $data['full_name'])) . '@ronakfire.com',
                    'password' => Hash::make($tempPassword),
                ]);
                
                // Access Control panel is now the single source of truth.
                // New accounts get the baseline core permissions directly;
                // role-based assignment has been removed.
                $this->applyBaseline($user);

                $employee->update(['user_id' => $user->id]);
            }

            return $employee->load(['department', 'designation', 'manager']);
        });
    }

    public function updateEmployee(Employee $employee, array $data): Employee
    {
        $this->processUploads($data);

        $employee->update($data);

        if (isset($data['designation_id']) && $employee->user) {
            // Access Control panel is now the single source of truth; ensure the
            // linked account always carries the baseline core permissions directly
            // (no role assignment).
            $this->applyBaseline($employee->user);
        }

        return $employee->fresh(['department', 'designation', 'manager']);
    }

    /**
     * Grant the baseline core permissions to a user as direct grants
     * (via the Access Control model). Only existing permissions are applied.
     */
    protected function applyBaseline(User $user): void
    {
        $baseline = config('access.baseline', []);
        $valid = \App\Models\Permission::whereIn('name', $baseline)->pluck('name')->all();
        $user->givePermissionTo($valid);
    }

    public function deleteEmployee(Employee $employee): void
    {
        $employee->delete();
    }
}
