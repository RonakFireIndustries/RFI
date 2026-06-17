<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\LeaveType;
use App\Services\LeaveTypeService;
use App\Http\Requests\StoreLeaveTypeRequest;
use App\Http\Requests\UpdateLeaveTypeRequest;
use App\Http\Resources\LeaveTypeResource;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LeaveTypeController extends Controller
{
    use ApiResponse;

    protected LeaveTypeService $service;

    public function __construct(LeaveTypeService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', LeaveType::class);

        $filters = $request->only(['status']);
        $perPage = (int) $request->input('per_page', 100);

        $types = $this->service->getLeaveTypes($filters, $perPage);

        return $this->success('Leave types retrieved successfully', [
            'leave_types' => LeaveTypeResource::collection($types),
            'meta' => [
                'current_page' => $types->currentPage(),
                'last_page' => $types->lastPage(),
                'per_page' => $types->perPage(),
                'total' => $types->total(),
            ]
        ]);
    }

    public function store(StoreLeaveTypeRequest $request): JsonResponse
    {
        $this->authorize('create', LeaveType::class);

        $type = $this->service->createLeaveType($request->validated());

        return $this->success('Leave type created successfully', [
            'leave_type' => new LeaveTypeResource($type)
        ], 201);
    }

    public function show(LeaveType $leaveType): JsonResponse
    {
        $this->authorize('view', $leaveType);

        return $this->success('Leave type retrieved successfully', [
            'leave_type' => new LeaveTypeResource($leaveType)
        ]);
    }

    public function update(UpdateLeaveTypeRequest $request, LeaveType $leaveType): JsonResponse
    {
        $this->authorize('update', $leaveType);

        $type = $this->service->updateLeaveType($leaveType, $request->validated());

        return $this->success('Leave type updated successfully', [
            'leave_type' => new LeaveTypeResource($type)
        ]);
    }

    public function destroy(LeaveType $leaveType): JsonResponse
    {
        $this->authorize('delete', $leaveType);

        $this->service->deleteLeaveType($leaveType);

        return $this->success('Leave type deleted successfully');
    }
}
