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
        return response()->json([
            'success' => true,
            'data' => $user->roles
        ]);
    }

    public function assignRole(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name',
        ]);

        $user->assignRole($request->roles);

        return response()->json([
            'success' => true,
            'message' => 'Roles assigned successfully',
            'data' => $user->roles
        ]);
    }

    public function removeRole(User $user, $roleId): JsonResponse
    {
        $role = Role::findOrFail($roleId);
        
        if ($role->name === 'Super Admin' && $user->id === 1) { // Basic safeguard
             return response()->json([
                'success' => false,
                'message' => 'Cannot remove Super Admin role from primary admin user'
            ], 403);
        }

        $user->removeRole($role);

        return response()->json([
            'success' => true,
            'message' => 'Role removed successfully'
        ]);
    }

    public function getPermissions(User $user): JsonResponse
    {
        // Get direct permissions and permissions via roles
        $permissions = $user->getAllPermissions();
        
        return response()->json([
            'success' => true,
            'data' => $permissions
        ]);
    }

    public function assignPermission(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $user->givePermissionTo($request->permissions);

        return response()->json([
            'success' => true,
            'message' => 'Permissions assigned successfully',
            'data' => $user->getAllPermissions()
        ]);
    }

    public function removePermission(User $user, $permissionId): JsonResponse
    {
        $permission = Permission::findOrFail($permissionId);
        $user->revokePermissionTo($permission);

        return response()->json([
            'success' => true,
            'message' => 'Permission removed successfully'
        ]);
    }
}
