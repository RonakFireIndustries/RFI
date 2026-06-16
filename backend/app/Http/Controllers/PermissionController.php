<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    public function index()
    {
        $perms = Permission::orderBy('name')->pluck('name');
        return response()->json(['success' => true, 'message' => 'Success', 'data' => $perms]);
    }

    public function roles()
    {
        $roles = Role::with('permissions')->get()->map(function ($role) {
            return [
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name'),
            ];
        });

        return response()->json(['success' => true, 'message' => 'Success', 'data' => $roles]);
    }

    public function updateRolePermissions(Request $request, Role $role)
    {
        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $role->syncPermissions($validated['permissions']);

        return response()->json(['success' => true, 'message' => 'Role permissions updated successfully', 'data' => [
            'role' => [
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name'),
            ],
        ]]);
    }
}
