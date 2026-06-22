<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use App\Models\PermissionScope;
use App\Models\RoleTemplate;
use App\Models\SystemException;
use App\Models\AuditSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleConfigController extends Controller
{
    public function scopes()
    {
        $scopes = PermissionScope::orderBy('level')->get();
        if ($scopes->isEmpty()) {
            $defaults = [
                ['name' => 'self', 'label' => 'Self', 'description' => 'Own records only', 'level' => 0],
                ['name' => 'team', 'label' => 'Team', 'description' => 'Team members', 'level' => 1],
                ['name' => 'department', 'label' => 'Department', 'description' => 'Entire department', 'level' => 2],
                ['name' => 'branch', 'label' => 'Branch', 'description' => 'Branch-wide', 'level' => 3],
                ['name' => 'region', 'label' => 'Region', 'description' => 'Regional access', 'level' => 4],
                ['name' => 'organization', 'label' => 'Organization', 'description' => 'Entire organization', 'level' => 5],
                ['name' => 'global', 'label' => 'Global', 'description' => 'All access', 'level' => 6],
            ];
            foreach ($defaults as $s) {
                PermissionScope::create($s);
            }
            $scopes = PermissionScope::orderBy('level')->get();
        }
        return response()->json(['success' => true, 'data' => $scopes]);
    }

    public function templates()
    {
        $templates = RoleTemplate::where('is_active', true)->get();
        return response()->json(['success' => true, 'data' => $templates]);
    }

    public function applyTemplate(Request $request, Role $role)
    {
        $request->validate(['template_id' => 'required|exists:role_templates,id']);
        $template = RoleTemplate::findOrFail($request->template_id);
        if (!$template->permissions) {
            return response()->json(['success' => false, 'message' => 'Template has no permissions defined'], 400);
        }
        $perms = Permission::whereIn('name', $template->permissions)->pluck('id')->toArray();
        $role->permissions()->sync($perms);
        return response()->json([
            'success' => true,
            'message' => 'Template applied successfully',
            'data' => $role->load('permissions'),
        ]);
    }

    public function exceptions()
    {
        $exceptions = SystemException::all();
        return response()->json(['success' => true, 'data' => $exceptions]);
    }

    public function updateException(Request $request, SystemException $exception)
    {
        $validated = $request->validate([
            'is_enabled' => 'sometimes|boolean',
            'requires_approval' => 'sometimes|boolean',
            'allowed_roles' => 'sometimes|array',
            'allowed_roles.*' => 'string',
        ]);
        $exception->update($validated);
        return response()->json([
            'success' => true,
            'message' => 'Exception updated',
            'data' => $exception->fresh(),
        ]);
    }

    public function auditSettings(Request $request, Role $role)
    {
        $settings = AuditSetting::firstOrCreate(
            ['configurable_type' => 'role', 'configurable_id' => $role->id],
            [
                'logging_verbosity' => 'medium',
                'retention_days' => 90,
                'reason_required' => false,
                'change_tracking_enabled' => true,
                'snapshot_creation_enabled' => false,
            ]
        );
        return response()->json(['success' => true, 'data' => $settings]);
    }

    public function updateAuditSettings(Request $request, Role $role)
    {
        $validated = $request->validate([
            'logging_verbosity' => 'sometimes|in:low,medium,high,critical',
            'retention_days' => 'sometimes|integer|min:1|max:3650',
            'reason_required' => 'sometimes|boolean',
            'change_tracking_enabled' => 'sometimes|boolean',
            'snapshot_creation_enabled' => 'sometimes|boolean',
        ]);
        $settings = AuditSetting::updateOrCreate(
            ['configurable_type' => 'role', 'configurable_id' => $role->id],
            $validated
        );
        return response()->json([
            'success' => true,
            'message' => 'Audit settings updated',
            'data' => $settings->fresh(),
        ]);
    }

    public function roleSummary(Role $role)
    {
        $role->load('permissions');
        $userCount = User::role($role->name)->count();
        $totalNodes = $role->permissions->count();

        $permissionGroups = [];
        $modules = [];
        foreach ($role->permissions as $perm) {
            $parts = explode('.', $perm->name);
            $module = $parts[0] ?? 'general';
            $action = $parts[1] ?? 'view';
            if (!isset($modules[$module])) {
                $modules[$module] = ['view' => false, 'create' => false, 'edit' => false, 'delete' => false];
            }
            if (in_array($action, ['view', 'create', 'edit', 'delete'])) {
                $modules[$module][$action] = true;
                if ($action === 'view' && !isset($modules[$module]['read'])) $modules[$module]['read'] = true;
                if ($action === 'create' && !isset($modules[$module]['write'])) $modules[$module]['write'] = true;
            }
            if (str_starts_with($action, 'manage') || $action === 'write') {
                $modules[$module]['write'] = true;
                $modules[$module]['read'] = true;
            }
            $permissionGroups[$module][] = $perm;
        }

        $highestScope = PermissionScope::orderByDesc('level')->first();
        $accessLevel = 'Basic';
        $count = $role->permissions->count();
        if ($count > 40) $accessLevel = 'Full System';
        elseif ($count > 25) $accessLevel = 'Advanced';
        elseif ($count > 10) $accessLevel = 'Standard';
        elseif ($count > 3) $accessLevel = 'Limited';

        return response()->json([
            'success' => true,
            'data' => [
                'user_count' => $userCount,
                'total_nodes' => $totalNodes,
                'access_level' => $accessLevel,
                'permission_groups' => $permissionGroups,
                'modules' => $modules,
            ],
        ]);
    }

    public function permissionsByModule()
    {
        $all = Permission::all();
        $grouped = [];
        foreach ($all as $perm) {
            $parts = explode('.', $perm->name);
            $module = $parts[0] ?? 'general';
            $grouped[$module][] = $perm;
        }
        return response()->json(['success' => true, 'data' => $grouped]);
    }

    public function dependencies()
    {
        $deps = DB::table('permission_dependencies')
            ->join('permissions as p1', 'permission_dependencies.permission_id', '=', 'p1.id')
            ->join('permissions as p2', 'permission_dependencies.depends_on_permission_id', '=', 'p2.id')
            ->select('p1.name as permission', 'p2.name as depends_on', 'permission_dependencies.type')
            ->get();
        return response()->json(['success' => true, 'data' => $deps]);
    }
}
