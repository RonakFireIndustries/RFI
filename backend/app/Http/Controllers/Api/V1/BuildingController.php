<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Services\BuildingService;
use App\Services\BuildingConversionService;
use App\Http\Requests\StoreBuildingRequest;
use App\Http\Requests\UpdateBuildingRequest;
use App\Http\Resources\BuildingResource;
use App\Http\Resources\SiteResource;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BuildingController extends Controller
{
    use ApiResponse;

    protected BuildingService $buildingService;

    public function __construct(BuildingService $buildingService)
    {
        $this->buildingService = $buildingService;
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('buildings.view');

        $filters = $request->only(['search', 'status', 'site_id']);
        $perPage = (int) $request->input('per_page', 15);

        $buildings = $this->buildingService->listBuildings($filters, $perPage);

        return $this->success('Buildings retrieved successfully', [
            'buildings' => BuildingResource::collection($buildings),
            'pagination' => [
                'total' => $buildings->total(),
                'per_page' => $buildings->perPage(),
                'current_page' => $buildings->currentPage(),
                'last_page' => $buildings->lastPage(),
            ]
        ]);
    }

    public function store(StoreBuildingRequest $request): JsonResponse
    {
        $this->authorize('buildings.create');

        $building = $this->buildingService->createBuilding($request->validated());

        return $this->success('Building created successfully', [
            'building' => new BuildingResource($building)
        ], [], 201);
    }

    public function show(Building $building): JsonResponse
    {
        $this->authorize('buildings.view');

        $building->load('site', 'fireSystemsConfig');

        return $this->success('Building retrieved successfully', [
            'building' => new BuildingResource($building)
        ]);
    }

    public function update(UpdateBuildingRequest $request, Building $building): JsonResponse
    {
        $this->authorize('buildings.update');

        $building = $this->buildingService->updateBuilding($building, $request->validated());

        return $this->success('Building updated successfully', [
            'building' => new BuildingResource($building)
        ]);
    }

    public function destroy(Building $building): JsonResponse
    {
        $this->authorize('buildings.delete');

        $this->buildingService->deleteBuilding($building);

        return $this->success('Building deleted successfully');
    }

    /**
     * Promote a building into a working Site.
     *
     * Idempotent: converting an already-converted building refreshes the Site
     * it produced rather than creating a second one.
     */
    public function convertToSite(Building $building, BuildingConversionService $conversion): JsonResponse
    {
        $this->authorize('sites.create');

        $result = $conversion->convert($building);

        $message = $result['already_converted']
            ? 'Building refreshed on its existing site'
            : ($result['created']
                ? 'Building converted to a new site successfully'
                : 'Building linked to the existing site successfully');

        return $this->success($message, [
            'building' => new BuildingResource($result['building']),
            'site' => new SiteResource($result['site']),
            'created' => $result['created'],
            'already_converted' => $result['already_converted'],
            'children_moved' => $result['children_moved'],
        ]);
    }
}
