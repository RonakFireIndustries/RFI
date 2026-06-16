<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    public function index()
    {
        return Permission::orderBy('name')->pluck('name');
    }

    public function roles()
    {
        return Role::with('permissions')->get()->map(function ($role) {
            return [
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name'),
            ];
        });
    }

    public function updateRolePermissions(Request $request, Role $role)
    {
        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $role->syncPermissions($validated['permissions']);

        return response()->json([
            'message' => 'Role permissions updated successfully',
            'role' => [
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name'),
            ],
        ]);
    }
}
