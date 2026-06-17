<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Services\EmployeeService;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EmployeeController extends Controller
{
    use ApiResponse;

    protected EmployeeService $employeeService;

    public function __construct(EmployeeService $employeeService)
    {
        $this->employeeService = $employeeService;
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Employee::class);

        $filters = $request->only([
            'search', 'department_id', 'designation_id', 'status', 'manager_id'
        ]);
        $perPage = (int) $request->input('per_page', 15);

        $employees = $this->employeeService->getEmployees($filters, $perPage);

        return $this->success('Employees retrieved successfully', [
            'employees' => EmployeeResource::collection($employees),
            'pagination' => [
                'total' => $employees->total(),
                'per_page' => $employees->perPage(),
                'current_page' => $employees->currentPage(),
                'last_page' => $employees->lastPage(),
            ]
        ]);
    }

    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        $this->authorize('create', Employee::class);

        $employee = $this->employeeService->createEmployee($request->validated());

        return $this->success('Employee created successfully', [
            'employee' => new EmployeeResource($employee)
        ], 201);
    }

    public function show(Employee $employee): JsonResponse
    {
        $this->authorize('view', $employee);

        $employee->load(['department', 'designation', 'manager', 'user']);

        return $this->success('Employee retrieved successfully', [
            'employee' => new EmployeeResource($employee)
        ]);
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee): JsonResponse
    {
        $this->authorize('update', $employee);

        $employee = $this->employeeService->updateEmployee($employee, $request->validated());

        return $this->success('Employee updated successfully', [
            'employee' => new EmployeeResource($employee)
        ]);
    }

    public function destroy(Employee $employee): JsonResponse
    {
        $this->authorize('delete', $employee);

        $this->employeeService->deleteEmployee($employee);

        return $this->success('Employee deleted successfully');
    }

    public function subordinates(Employee $employee): JsonResponse
    {
        $this->authorize('view', $employee);

        $subordinates = $employee->subordinates()->with(['department', 'designation'])->get();

        return $this->success('Subordinates retrieved successfully', [
            'subordinates' => EmployeeResource::collection($subordinates)
        ]);
    }

    public function manager(Employee $employee): JsonResponse
    {
        $this->authorize('view', $employee);

        $manager = $employee->manager()->with(['department', 'designation'])->first();

        return $this->success('Manager retrieved successfully', [
            'manager' => $manager ? new EmployeeResource($manager) : null
        ]);
    }
}
