<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserAccessController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('manage_users');

        $users = User::with(['roles:id,name', 'employee:id,user_id,emp_id,full_name,department_id,designation_id', 'employee.department:id,name', 'employee.designation:id,name'])
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->orderBy('name')
            ->get();

        $data = $users->map(fn (User $user) => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'employee' => $user->employee ? [
                'id' => $user->employee->id,
                'emp_id' => $user->employee->emp_id,
                'department' => $user->employee->department?->name,
                'designation' => $user->employee->designation?->name,
            ] : null,
            'roles' => $user->roles->map(fn (Role $role) => [
                'id' => $role->id,
                'name' => $role->name,
            ]),
            'permissions_count' => $user->getDirectPermissions()->count(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

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
        $user->roles()->syncWithoutDetaching($roleIds);

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
        $permissions = $user->getDirectPermissions();

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

        $user->givePermissionTo($request->permissions);

        return response()->json([
            'success' => true,
            'message' => 'Permissions assigned successfully',
            'data' => $user->getDirectPermissions()
        ]);
    }

    public function removePermission(User $user, $permissionId): JsonResponse
    {
        $this->authorize('manage_users');
        $permission = Permission::findOrFail($permissionId);
        $user->revokePermissionTo($permission);

        return response()->json([
            'success' => true,
            'message' => 'Permission removed successfully'
        ]);
    }
}
