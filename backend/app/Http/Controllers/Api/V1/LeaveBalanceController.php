<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\LeaveBalance;
use App\Models\Employee;
use App\Services\LeaveBalanceService;
use App\Http\Resources\LeaveBalanceResource;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LeaveBalanceController extends Controller
{
    use ApiResponse;

    protected LeaveBalanceService $service;

    public function __construct(LeaveBalanceService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['employee_id', 'year']);
        $perPage = (int) $request->input('per_page', 50);

        $balances = $this->service->getBalances($filters, $perPage);

        return $this->success('Leave balances retrieved successfully', [
            'leave_balances' => LeaveBalanceResource::collection($balances)->resolve($request),
            'meta' => [
                'current_page' => $balances->currentPage(),
                'last_page' => $balances->lastPage(),
                'per_page' => $balances->perPage(),
                'total' => $balances->total(),
            ]
        ]);
    }

    public function initialize(Request $request, Employee $employee): JsonResponse
    {
        $year = $request->input('year', date('Y'));
        
        $this->service->initializeBalances($employee, $year);

        return $this->success('Leave balances initialized successfully.');
    }
}
