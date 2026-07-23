<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use App\Http\Requests\RoleStoreRequest;
use App\Http\Requests\RoleUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('manage_roles');
        $roles = Role::with('permissions')->get();
        return response()->json([
            'success' => true,
            'data' => $roles
        ]);
    }

    public function store(RoleStoreRequest $request): JsonResponse
    {
        $this->authorize('manage_roles');
        $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);

        if ($request->has('permissions')) {
            $perms = Permission::whereIn('name', $request->permissions)->pluck('id')->toArray();
            $role->permissions()->sync($perms);
        }

        return response()->json([
            'success' => true,
            'message' => 'Role created successfully',
            'data' => $role->load('permissions')
        ], 201);
    }

    public function show(Role $role): JsonResponse
    {
        $this->authorize('manage_roles');
        return response()->json([
            'success' => true,
            'data' => $role->load('permissions')
        ]);
    }

    public function update(RoleUpdateRequest $request, Role $role): JsonResponse
    {
        $this->authorize('manage_roles');
        $role->update(['name' => $request->name]);

        if ($request->has('permissions')) {
            $perms = Permission::whereIn('name', $request->permissions)->pluck('id')->toArray();
            $role->permissions()->sync($perms);
        }

        return response()->json([
            'success' => true,
            'message' => 'Role updated successfully',
            'data' => $role->load('permissions')
        ]);
    }

    public function destroy(Role $role): JsonResponse
    {
        $this->authorize('manage_roles');
        if ($role->name === 'Super Admin') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete Super Admin role'
            ], 403);
        }

        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Role deleted successfully'
        ]);
    }
}