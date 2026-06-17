<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Designation;
use App\Services\DesignationService;
use App\Http\Requests\StoreDesignationRequest;
use App\Http\Requests\UpdateDesignationRequest;
use App\Http\Resources\DesignationResource;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DesignationController extends Controller
{
    use ApiResponse;

    protected DesignationService $designationService;

    public function __construct(DesignationService $designationService)
    {
        $this->designationService = $designationService;
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Designation::class);

        $filters = $request->only(['search', 'department_id']);
        $perPage = (int) $request->input('per_page', 15);

        $designations = $this->designationService->getDesignations($filters, $perPage);

        return $this->success('Designations retrieved successfully', [
            'designations' => DesignationResource::collection($designations),
            'pagination' => [
                'total' => $designations->total(),
                'per_page' => $designations->perPage(),
                'current_page' => $designations->currentPage(),
                'last_page' => $designations->lastPage(),
            ]
        ]);
    }

    public function store(StoreDesignationRequest $request): JsonResponse
    {
        $this->authorize('create', Designation::class);

        $designation = $this->designationService->createDesignation($request->validated());

        return $this->success('Designation created successfully', [
            'designation' => new DesignationResource($designation->load('department'))
        ], 201);
    }

    public function show(Designation $designation): JsonResponse
    {
        $this->authorize('view', $designation);

        $designation->load(['department']);
        $designation->loadCount(['employees']);

        return $this->success('Designation retrieved successfully', [
            'designation' => new DesignationResource($designation)
        ]);
    }

    public function update(UpdateDesignationRequest $request, Designation $designation): JsonResponse
    {
        $this->authorize('update', $designation);

        $designation = $this->designationService->updateDesignation($designation, $request->validated());

        return $this->success('Designation updated successfully', [
            'designation' => new DesignationResource($designation)
        ]);
    }

    public function destroy(Designation $designation): JsonResponse
    {
        $this->authorize('delete', $designation);

        $this->designationService->deleteDesignation($designation);

        return $this->success('Designation deleted successfully');
    }
}
