<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Services\SiteService;
use App\Http\Requests\StoreSiteRequest;
use App\Http\Requests\UpdateSiteRequest;
use App\Http\Resources\SiteResource;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SiteController extends Controller
{
    use ApiResponse;

    protected SiteService $siteService;

    public function __construct(SiteService $siteService)
    {
        $this->siteService = $siteService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status']);
        $perPage = (int) $request->input('per_page', 15);

        $sites = $this->siteService->listSites($filters, $perPage);

        return $this->success('Sites retrieved successfully', [
            'sites' => SiteResource::collection($sites),
            'pagination' => [
                'total' => $sites->total(),
                'per_page' => $sites->perPage(),
                'current_page' => $sites->currentPage(),
                'last_page' => $sites->lastPage(),
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSiteRequest $request): JsonResponse
    {
        // StoreSiteRequest handles authorization internally via its authorize method.
        $site = $this->siteService->createSite($request->validated());

        return $this->success('Site created successfully', [
            'site' => new SiteResource($site)
        ], [], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Site $site): JsonResponse
    {
        $site->load('manager');

        return $this->success('Site retrieved successfully', [
            'site' => new SiteResource($site)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSiteRequest $request, Site $site): JsonResponse
    {
        // UpdateSiteRequest handles authorization internally via its authorize method.
        $site = $this->siteService->updateSite($site, $request->validated());

        return $this->success('Site updated successfully', [
            'site' => new SiteResource($site)
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Site $site): JsonResponse
    {
        $this->siteService->deleteSite($site);

        return $this->success('Site deleted successfully');
    }
}

