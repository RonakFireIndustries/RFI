<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Services\DepartmentService;
use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Http\Resources\DepartmentResource;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DepartmentController extends Controller
{
    use ApiResponse;

    protected DepartmentService $departmentService;

    public function __construct(DepartmentService $departmentService)
    {
        $this->departmentService = $departmentService;
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('department.view');
        $filters = $request->only(['search']);
        $perPage = (int) $request->input('per_page', 15);

        $departments = $this->departmentService->getDepartments($filters, $perPage);

        return $this->success('Departments retrieved successfully', [
            'departments' => DepartmentResource::collection($departments),
            'pagination' => [
                'total' => $departments->total(),
                'per_page' => $departments->perPage(),
                'current_page' => $departments->currentPage(),
                'last_page' => $departments->lastPage(),
            ]
        ]);
    }

    public function store(StoreDepartmentRequest $request): JsonResponse
    {
        $this->authorize('department.create');
        $department = $this->departmentService->createDepartment($request->validated());

        return $this->success('Department created successfully', [
            'department' => new DepartmentResource($department)
        ], 201);
    }

    public function show(Department $department): JsonResponse
    {
        $department->loadCount(['employees', 'designations']);

        return $this->success('Department retrieved successfully', [
            'department' => new DepartmentResource($department)
        ]);
    }

    public function update(UpdateDepartmentRequest $request, Department $department): JsonResponse
    {
        $this->authorize('department.edit');
        $department = $this->departmentService->updateDepartment($department, $request->validated());

        return $this->success('Department updated successfully', [
            'department' => new DepartmentResource($department)
        ]);
    }

    public function destroy(Department $department): JsonResponse
    {
        $this->authorize('department.delete');
        $this->departmentService->deleteDepartment($department);

        return $this->success('Department deleted successfully');
    }
}
