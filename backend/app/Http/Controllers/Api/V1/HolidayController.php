<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHolidayRequest;
use App\Http\Requests\UpdateHolidayRequest;
use App\Http\Resources\HolidayResource;
use App\Models\Holiday;
use App\Services\HolidayService;
use App\Traits\ApiResponse;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The company holiday calendar.
 *
 * A holiday declared here is what payroll, attendance and leave read to
 * decide which days are working days, so retiring one (status -> Inactive)
 * puts the working day back rather than deleting the history.
 */
class HolidayController extends Controller
{
    use ApiResponse;

    protected HolidayService $holidayService;

    public function __construct(HolidayService $holidayService)
    {
        $this->holidayService = $holidayService;
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('holidays.view');

        $filters = $request->only(['year', 'type', 'status', 'search', 'from', 'to']);
        $perPage = (int) $request->input('per_page', 15);

        $holidays = $this->holidayService->getHolidays($filters, $perPage);

        return $this->success('Holidays retrieved successfully', [
            'holidays' => HolidayResource::collection($holidays),
            'pagination' => [
                'total' => $holidays->total(),
                'per_page' => $holidays->perPage(),
                'current_page' => $holidays->currentPage(),
                'last_page' => $holidays->lastPage(),
            ],
        ]);
    }

    /**
     * The years available on the calendar, for the year filter.
     */
    public function years(): JsonResponse
    {
        $this->authorize('holidays.view');

        return $this->success('Holiday years retrieved successfully', [
            'years' => $this->holidayService->getYears(),
        ]);
    }

    public function store(StoreHolidayRequest $request): JsonResponse
    {
        $this->authorize('holidays.create');

        try {
            $holiday = $this->holidayService->createHoliday($request->validated());
        } catch (QueryException $e) {
            if (str_contains($e->getMessage(), 'UNIQUE') || str_contains($e->getMessage(), 'unique')) {
                return $this->error('The given data was invalid.', ['date' => ['This holiday date has already been taken.']], 422);
            }
            throw $e;
        }

        return $this->success('Holiday created successfully', [
            'holiday' => new HolidayResource($holiday),
        ], [], 201);
    }

    public function show(Holiday $holiday): JsonResponse
    {
        $this->authorize('holidays.view');

        return $this->success('Holiday retrieved successfully', [
            'holiday' => new HolidayResource($holiday),
        ]);
    }

    public function update(UpdateHolidayRequest $request, Holiday $holiday): JsonResponse
    {
        $this->authorize('holidays.update');

        try {
            $holiday = $this->holidayService->updateHoliday($holiday, $request->validated());
        } catch (QueryException $e) {
            if (str_contains($e->getMessage(), 'UNIQUE') || str_contains($e->getMessage(), 'unique')) {
                return $this->error('The given data was invalid.', ['date' => ['This holiday date has already been taken.']], 422);
            }
            throw $e;
        }

        return $this->success('Holiday updated successfully', [
            'holiday' => new HolidayResource($holiday),
        ]);
    }

    public function destroy(Holiday $holiday): JsonResponse
    {
        $this->authorize('holidays.delete');

        $this->holidayService->deleteHoliday($holiday);

        return $this->success('Holiday deleted successfully');
    }
}
