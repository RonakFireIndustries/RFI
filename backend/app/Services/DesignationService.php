<?php

namespace App\Services;

use App\Models\Designation;
use Illuminate\Pagination\LengthAwarePaginator;

class DesignationService
{
    public function getDesignations(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Designation::with('department')->withCount('employees');

        if (isset($filters['search']) && $filters['search']) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        if (isset($filters['department_id']) && $filters['department_id']) {
            $query->where('department_id', $filters['department_id']);
        }

        return $query->paginate($perPage);
    }

    public function createDesignation(array $data): Designation
    {
        return Designation::create($data);
    }

    public function updateDesignation(Designation $designation, array $data): Designation
    {
        $designation->update($data);
        return $designation->fresh('department');
    }

    public function deleteDesignation(Designation $designation): void
    {
        $designation->delete();
    }
}
