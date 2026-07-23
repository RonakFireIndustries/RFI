<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserAccessController extends Controller
{
    public function getRoles(User $user): JsonResponse
    {
        $this->authorize('manage_users');
        return response()->json([
            'success' => true,
            'data' => $user->roles
        ]);
    }

    public function assignRole(Request $request, User $user): JsonResponse
    {
        $this->authorize('manage_users');
        $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name',
        ]);

        $roleIds = Role::whereIn('name', $request->roles)->pluck('id')->toArray();
        $user->roles()->sync($roleIds);

        return response()->json([
            'success' => true,
            'message' => 'Roles assigned successfully',
            'data' => $user->roles
        ]);
    }

    public function removeRole(User $user, $roleId): JsonResponse
    {
        $this->authorize('manage_users');
        $role = Role::findOrFail($roleId);
        
        if ($role->name === 'Super Admin' && $user->id === 1) { // Basic safeguard
             return response()->json([
                'success' => false,
                'message' => 'Cannot remove Super Admin role from primary admin user'
            ], 403);
        }

        $user->roles()->detach($role->id);

        return response()->json([
            'success' => true,
            'message' => 'Role removed successfully'
        ]);
    }

    public function getPermissions(User $user): JsonResponse
    {
        $this->authorize('manage_users');
        $permissions = $user->roles->flatMap->permissions->unique('id');
        
        return response()->json([
            'success' => true,
            'data' => $permissions
        ]);
    }

    public function assignPermission(Request $request, User $user): JsonResponse
    {
        $this->authorize('manage_users');
        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $perms = Permission::whereIn('name', $request->permissions)->get();
        $roles = $user->roles;
        foreach ($roles as $role) {
            $role->permissions()->syncWithoutDetaching($perms->pluck('id')->toArray());
        }

        return response()->json([
            'success' => true,
            'message' => 'Permissions assigned successfully',
            'data' => $user->fresh()->roles->flatMap->permissions->unique('id')
        ]);
    }

    public function removePermission(User $user, $permissionId): JsonResponse
    {
        $this->authorize('manage_users');
        $permission = Permission::findOrFail($permissionId);
        foreach ($user->roles as $role) {
            $role->permissions()->detach($permission->id);
        }

        return response()->json([
            'success' => true,
            'message' => 'Permission removed successfully'
        ]);
    }
}
