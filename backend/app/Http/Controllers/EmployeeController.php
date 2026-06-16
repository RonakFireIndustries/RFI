<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    public function index()
    {
        return Employee::with('user.branch')->get()->map(function (Employee $employee) {
            $role = $employee->user?->getRoleNames()->first();
            $permissions = $employee->user?->getAllPermissions()->pluck('name') ?? collect();

            return array_merge($employee->toArray(), [
                'role' => $role,
                'permissions' => $permissions,
            ]);
        });
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'branch_id' => 'required|exists:branches,id',
            'department' => 'nullable|string',
            'salary' => 'nullable|numeric',
            'shift' => 'nullable|string',
            'joining_date' => 'nullable|date',
            'role' => 'nullable|string|exists:roles,name',
            'permissions' => 'sometimes|array',
            'permissions.*' => 'required_with:permissions|string|exists:permissions,name',
            'status' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'branch_id' => $validated['branch_id'],
            ]);

            if (!empty($validated['role'])) {
                $user->assignRole($validated['role']);
            }

            if (!empty($validated['permissions'])) {
                $user->syncPermissions($validated['permissions']);
            }

            $employee = Employee::create([
                'user_id' => $user->id,
                'department' => $validated['department'] ?? null,
                'salary' => $validated['salary'] ?? null,
                'shift' => $validated['shift'] ?? null,
                'joining_date' => $validated['joining_date'] ?? null,
                'status' => $validated['status'] ?? 'active',
            ]);

            return array_merge($employee->load('user.branch')->toArray(), [
                'role' => $user->getRoleNames()->first(),
                'permissions' => $user->getAllPermissions()->pluck('name'),
            ]);
        });
    }

    public function show(Employee $employee)
    {
        $employee->load('user.branch', 'attendances');

        return array_merge($employee->toArray(), [
            'role' => $employee->user?->getRoleNames()->first(),
            'permissions' => $employee->user?->getAllPermissions()->pluck('name') ?? collect(),
        ]);
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $employee->user_id,
            'branch_id' => 'sometimes|exists:branches,id',
            'department' => 'nullable|string',
            'salary' => 'nullable|numeric',
            'shift' => 'nullable|string',
            'joining_date' => 'nullable|date',
            'status' => 'nullable|string',
            'role' => 'nullable|string|exists:roles,name',
            'permissions' => 'sometimes|array',
            'permissions.*' => 'required_with:permissions|string|exists:permissions,name',
        ]);

        DB::transaction(function () use ($validated, $employee) {
            $userUpdates = [];
            if (isset($validated['name'])) {
                $userUpdates['name'] = $validated['name'];
            }
            if (isset($validated['email'])) {
                $userUpdates['email'] = $validated['email'];
            }
            if (isset($validated['branch_id'])) {
                $userUpdates['branch_id'] = $validated['branch_id'];
            }

            if (!empty($userUpdates)) {
                $employee->user->update($userUpdates);
            }

            if (isset($validated['role'])) {
                $employee->user->syncRoles([$validated['role']]);
            }

            if (isset($validated['permissions'])) {
                $employee->user->syncPermissions($validated['permissions']);
            }

            $employee->update([
                'department' => $validated['department'] ?? $employee->department,
                'salary' => $validated['salary'] ?? $employee->salary,
                'shift' => $validated['shift'] ?? $employee->shift,
                'joining_date' => $validated['joining_date'] ?? $employee->joining_date,
                'status' => $validated['status'] ?? $employee->status,
            ]);
        });

        $employee->refresh();

        return array_merge($employee->load('user.branch')->toArray(), [
            'role' => $employee->user?->getRoleNames()->first(),
            'permissions' => $employee->user?->getAllPermissions()->pluck('name') ?? collect(),
        ]);
    }

    public function destroy(Employee $employee)
    {
        DB::transaction(function () use ($employee) {
            $user = $employee->user;
            $employee->delete();
            if ($user) {
                $user->delete();
            }
        });

        return response()->noContent();
    }
}
