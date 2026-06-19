<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use App\Services\LeaveRequestService;
use App\Http\Requests\StoreLeaveRequest;
use App\Http\Requests\UpdateLeaveRequest;
use App\Http\Resources\LeaveResource;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class LeaveController extends Controller
{
    use ApiResponse;

    protected LeaveRequestService $service;

    public function __construct(LeaveRequestService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['employee_id', 'status', 'leave_type_id']);
        
        // All authenticated users can view leaves

        $perPage = (int) $request->input('per_page', 15);
        $leaves = $this->service->getLeaves($filters, $perPage);

        return $this->success('Leave requests retrieved successfully', [
            'leave_requests' => LeaveResource::collection($leaves)->resolve($request),
            'meta' => [
                'current_page' => $leaves->currentPage(),
                'last_page' => $leaves->lastPage(),
                'per_page' => $leaves->perPage(),
                'total' => $leaves->total(),
            ]
        ]);
    }

    public function store(StoreLeaveRequest $request): JsonResponse
    {
        try {
            $leave = $this->service->createLeave($request->validated());
            $leave->load(['employee.user', 'employee.department', 'leaveType', 'approver']);

            return $this->success('Leave request created successfully', [
                'leave_request' => (new LeaveResource($leave))->resolve($request)
            ], 201);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), [], 422);
        }
    }

    public function show(Leave $leave): JsonResponse
    {
        $leave->load(['employee.user', 'employee.department', 'leaveType', 'approver', 'histories.user']);

        return $this->success('Leave request retrieved successfully', [
            'leave_request' => (new LeaveResource($leave))->resolve(request())
        ]);
    }

    public function update(UpdateLeaveRequest $request, Leave $leave): JsonResponse
    {
        try {
            $updatedLeave = $this->service->updateLeave($leave, $request->validated());
            $updatedLeave->load(['employee.user', 'employee.department', 'leaveType', 'approver']);

            return $this->success('Leave request updated successfully', [
                'leave_request' => (new LeaveResource($updatedLeave))->resolve($request)
            ]);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), [], 422);
        }
    }

    public function destroy(Leave $leave): JsonResponse
    {
        $leave->delete();
        return $this->success('Leave request deleted successfully');
    }

    public function approve(Request $request, Leave $leave): JsonResponse
    {
        $request->validate(['comments' => 'nullable|string']);

        try {
            $updatedLeave = $this->service->approve($leave, $request->comments);
            $updatedLeave->load(['employee.user', 'employee.department', 'leaveType', 'approver']);
            return $this->success('Leave approved successfully', [
                'leave_request' => (new LeaveResource($updatedLeave))->resolve($request)
            ]);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), [], 422);
        }
    }

    public function reject(Request $request, Leave $leave): JsonResponse
    {
        $request->validate(['comments' => 'required|string']);

        $updatedLeave = $this->service->reject($leave, $request->comments);
        $updatedLeave->load(['employee.user', 'employee.department', 'leaveType', 'approver']);
        return $this->success('Leave rejected successfully', [
            'leave_request' => (new LeaveResource($updatedLeave))->resolve($request)
        ]);
    }

    public function cancel(Request $request, Leave $leave): JsonResponse
    {
        $request->validate(['comments' => 'required|string']);

        $updatedLeave = $this->service->cancel($leave, $request->comments);
        $updatedLeave->load(['employee.user', 'employee.department', 'leaveType', 'approver']);
        return $this->success('Leave cancelled successfully', [
            'leave_request' => (new LeaveResource($updatedLeave))->resolve($request)
        ]);
    }
}
