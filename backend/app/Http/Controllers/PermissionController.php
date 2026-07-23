<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Http\Requests\PermissionStoreRequest;
use App\Http\Requests\PermissionUpdateRequest;
use Illuminate\Http\JsonResponse;

class PermissionController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('manage_permissions');
        $permissions = Permission::all();
        return response()->json([
            'success' => true,
            'data' => $permissions
        ]);
    }

    public function store(PermissionStoreRequest $request): JsonResponse
    {
        $this->authorize('manage_permissions');
        $permission = Permission::create(['name' => $request->name, 'guard_name' => 'web']);

        return response()->json([
            'success' => true,
            'message' => 'Permission created successfully',
            'data' => $permission
        ], 201);
    }

    public function show(Permission $permission): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $permission
        ]);
    }

    public function update(PermissionUpdateRequest $request, Permission $permission): JsonResponse
    {
        $this->authorize('manage_permissions');
        $permission->update(['name' => $request->name]);

        return response()->json([
            'success' => true,
            'message' => 'Permission updated successfully',
            'data' => $permission
        ]);
    }

    public function destroy(Permission $permission): JsonResponse
    {
        $this->authorize('manage_permissions');
        $permission->delete();

        return response()->json([
            'success' => true,
            'message' => 'Permission deleted successfully'
        ]);
    }
}
