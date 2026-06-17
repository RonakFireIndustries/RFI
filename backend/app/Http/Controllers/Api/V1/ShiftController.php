<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Services\ShiftService;
use App\Http\Requests\StoreShiftRequest;
use App\Http\Requests\UpdateShiftRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ShiftController extends Controller
{
    use ApiResponse;

    protected ShiftService $shiftService;

    public function __construct(ShiftService $shiftService)
    {
        $this->shiftService = $shiftService;
    }

    /**
     * Display a listing of the shifts.
     */
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Shift::class);

        $shifts = $this->shiftService->getAllShifts();

        return $this->success('Shifts retrieved successfully', [
            'shifts' => $shifts
        ]);
    }

    /**
     * Store a newly created shift.
     */
    public function store(StoreShiftRequest $request): JsonResponse
    {
        $this->authorize('create', Shift::class);

        $shift = $this->shiftService->createShift($request->validated());

        return $this->success('Shift created successfully', [
            'shift' => $shift
        ], 201);
    }

    /**
     * Display the specified shift.
     */
    public function show(Shift $shift): JsonResponse
    {
        $this->authorize('view', $shift);

        return $this->success('Shift retrieved successfully', [
            'shift' => $shift
        ]);
    }

    /**
     * Update the specified shift.
     */
    public function update(UpdateShiftRequest $request, Shift $shift): JsonResponse
    {
        $this->authorize('update', $shift);

        $shift = $this->shiftService->updateShift($shift, $request->validated());

        return $this->success('Shift updated successfully', [
            'shift' => $shift
        ]);
    }

    /**
     * Remove the specified shift.
     */
    public function destroy(Shift $shift): JsonResponse
    {
        $this->authorize('delete', $shift);

        $this->shiftService->deleteShift($shift);

        return $this->success('Shift deleted successfully');
    }
}
