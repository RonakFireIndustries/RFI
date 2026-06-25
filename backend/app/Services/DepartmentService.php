<?php

namespace App\Services;

use App\Models\Department;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class DepartmentService
{
    /**
     * Get paginated and filtered departments.
     */
    public function getDepartments(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Department::withCount(['employees', 'designations']);

        if (isset($filters['search']) && $filters['search']) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        return $query->paginate($perPage);
    }

    /**
     * Create a new department.
     */
    public function createDepartment(array $data): Department
    {
        return Department::create($data);
    }

    /**
     * Update an existing department.
     */
    public function updateDepartment(Department $department, array $data): Department
    {
        $department->update($data);
        return $department->fresh();
    }

    /**
     * Delete a department.
     */
    public function deleteDepartment(Department $department): void
    {
        $department->delete();
    }
}
