<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $employees = Employee::with(['department', 'designation', 'reportingManager'])->get();
        return response()->json([
            'status' => 'success',
            'data' => $employees
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'emp_id' => 'required|string|unique:employees,emp_id',
            'full_name' => 'required|string|max:255',
            'joining_date' => 'required|date',
            'department_id' => 'nullable|exists:departments,id',
            'designation_id' => 'nullable|exists:designations,id',
            'email' => 'nullable|email|unique:users,email' // Assuming user creation is handled separately or joined
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $employee = Employee::create($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Employee created successfully.',
            'data' => $employee
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $employee = Employee::with(['department', 'designation', 'reportingManager'])->find($id);

        if (!$employee) {
            return response()->json([
                'status' => 'error',
                'message' => 'Employee not found.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $employee
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $employee = Employee::find($id);

        if (!$employee) {
            return response()->json([
                'status' => 'error',
                'message' => 'Employee not found.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'emp_id' => 'string|unique:employees,emp_id,'.$id,
            'full_name' => 'string|max:255',
            'joining_date' => 'date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $employee->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Employee updated successfully.',
            'data' => $employee
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $employee = Employee::find($id);

        if (!$employee) {
            return response()->json([
                'status' => 'error',
                'message' => 'Employee not found.'
            ], 404);
        }

        $employee->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Employee deleted successfully.'
        ]);
    }
}
