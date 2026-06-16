<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Http\Resources\DepartmentResource;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::with('branch', 'head')->get();
        return response()->json([
            'success' => true,
            'message' => 'Departments retrieved',
            'data' => DepartmentResource::collection($departments)
        ], 200);
    }

    public function show(Department $department)
    {
        $department->load('branch', 'head', 'employees');
        return response()->json([
            'success' => true,
            'message' => 'Department retrieved',
            'data' => new DepartmentResource($department)
        ], 200);
    }

    public function store(StoreDepartmentRequest $request)
    {
        $data = $request->validated();
        $department = Department::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Department created',
            'data' => new DepartmentResource($department)
        ], 201);
    }

    public function update(UpdateDepartmentRequest $request, Department $department)
    {
        $data = $request->validated();
        $department->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Department updated',
            'data' => new DepartmentResource($department)
        ], 200);
    }

    public function destroy(Department $department)
    {
        $department->delete();

        return response()->json([
            'success' => true,
            'message' => 'Department deleted',
            'data' => null
        ], 200);
    }
}
