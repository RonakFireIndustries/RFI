<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Permission;
use App\Support\Access;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccessControlController extends Controller
{
    /**
     * Return the module/action definitions (single source of truth) plus the
     * action labels the UI needs. Super Admin only.
     */
    public function definitions(): JsonResponse
    {
        $this->authorizeAnAccessControl();

        $labels = config('access.actions', []);
        $categories = [];
        foreach (Access::categories() as $categoryName => $modules) {
            $list = [];
            foreach ($modules as $module => $def) {
                $list[] = [
                    'module' => $module,
                    'label' => $def['label'],
                    'actions' => array_map(function ($action) use ($labels, $module) {
                        return [
                            'key' => $action,
                            'label' => $labels[$action] ?? ucfirst($action),
                            'permission' => Access::permissionName($module, $action),
                        ];
                    }, $def['actions']),
                ];
            }
            $categories[] = [
                'category' => $categoryName,
                'modules' => $list,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'categories' => $categories,
            ],
        ]);
    }

    /**
     * List users who can be granted access, with their granted canonical
     * permission names. Super Admin only.
     */
    public function users(): JsonResponse
    {
        $this->authorizeAnAccessControl();

        $users = User::with(['employee:id,user_id,emp_id,full_name,department_id,designation_id',
            'employee.department:id,name', 'employee.designation:id,name'])
            ->orderBy('name')
            ->get();

        $data = $users->map(function (User $user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_super_admin' => Access::isSuperAdmin($user),
                // Direct grants only. The Access page edits direct permissions, so
                // showing role-derived (effective) grants here made removed ones
                // reappear in the list after a refresh (a role would re-supply them).
                'permissions' => $user->getDirectPermissions()->pluck('name')->values()->all(),
                'employee' => $user->employee ? [
                    'id' => $user->employee->id,
                    'emp_id' => $user->employee->emp_id,
                    'department' => $user->employee->department?->name,
                    'designation' => $user->employee->designation?->name,
                ] : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * List a single user's granted permissions (HINT: effective + direct).
     */
    public function userPermissions(User $user): JsonResponse
    {
        $this->authorizeAnAccessControl();

        $direct = $user->getDirectPermissions()->pluck('name')->values();

        return response()->json([
            'success' => true,
            'data' => [
                'user_id' => $user->id,
                'direct' => $direct,
                'effective' => Access::permissionNamesFor($user),
            ],
        ]);
    }

    /**
     * Assign (or replace) a user's direct permissions.
     * Expects { permissions: ['suppliers.view', ...] }.
     */
    public function updatePermissions(Request $request, User $user): JsonResponse
    {
        $this->authorizeAnAccessControl();

        $request->validate([
            'permissions' => ['array'],
            'permissions.*' => ['distinct', 'string'],
        ]);

        $names = $request->input('permissions', []);

        // Only sync permissions that actually exist, so a stale/missing name
        // (e.g. config drift between the UI build and the server) doesn't throw
        // a PermissionDoesNotExist 500. Unknown names are silently ignored.
        $validNames = Permission::whereIn('name', $names)->pluck('name')->all();

        DB::transaction(function () use ($user, $validNames) {
            $user->syncPermissions($validNames);
        });

        return response()->json([
            'success' => true,
            'message' => 'Access updated successfully.',
            'data' => [
                'direct' => $user->getDirectPermissions()->pluck('name')->values(),
            ],
        ]);
    }

    /**
     * Grant a single permission to a user.
     */
    public function grantPermission(Request $request, User $user): JsonResponse
    {
        $this->authorizeAnAccessControl();

        $request->validate([
            'permission' => ['required', 'string', 'exists:permissions,name'],
        ]);

        $user->givePermissionTo($request->input('permission'));

        return response()->json([
            'success' => true,
            'message' => 'Permission granted.',
            'data' => [
                'direct' => $user->getDirectPermissions()->pluck('name')->values(),
            ],
        ]);
    }

    /**
     * Revoke a single (direct) permission from a user by canonical name.
     */
    public function revokePermission(Request $request, User $user): JsonResponse
    {
        $this->authorizeAnAccessControl();

        $request->validate([
            'permission' => ['required', 'string'],
        ]);

        $permission = Permission::where('name', $request->input('permission'))->first();
        if ($permission) {
            $user->revokePermissionTo($permission);
        }

        return response()->json([
            'success' => true,
            'message' => 'Permission revoked.',
            'data' => [
                'direct' => $user->getDirectPermissions()->pluck('name')->values(),
            ],
        ]);
    }

    /**
     * Access Control page is gated to super admins (or holders of the
     * access-control.manage permission).
     */
    protected function authorizeAnAccessControl(): void
    {
        $user = request()->user();

        if (!$user || !Access::isSuperAdmin($user)) {
            if (!$user || !$user->hasPermissionTo('access-control.manage')) {
                abort(403, 'This action is unauthorized.');
            }
        }
    }
}
