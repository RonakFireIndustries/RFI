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
                $user = User::create([
                    'name' => $data['full_name'],
                    'email' => strtolower(str_replace(' ', '.', $data['full_name'])) . '@ronakfire.com',
                    'password' => Hash::make('password123'),
                ]);
                
                // Assign role based on designation name, explicit role field, or default
                $roleName = 'Employee';
                if (!empty($data['designation_id'])) {
                    $designation = \App\Models\Designation::find($data['designation_id']);
                    if ($designation) $roleName = $designation->name;
                } elseif (!empty($data['role'])) {
                    $roleName = $data['role'];
                }
                $role = \App\Models\Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
                try {
                    $user->roles()->sync([$role->id]);
                } catch (\Exception $e) {}

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
            $designation = \App\Models\Designation::find($data['designation_id']);
            if ($designation) {
                $role = \App\Models\Role::firstOrCreate(['name' => $designation->name, 'guard_name' => 'web']);
                $employee->user->roles()->sync([$role->id]);
            }
        }

        return $employee->fresh(['department', 'designation', 'manager']);
    }

    public function deleteEmployee(Employee $employee): void
    {
        $employee->delete();
    }
}
