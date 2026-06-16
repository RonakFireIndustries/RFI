<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Site;
use App\Http\Resources\SiteResource;
use App\Http\Requests\StoreSiteRequest;
use App\Http\Requests\UpdateSiteRequest;

class SiteController extends Controller
{
    public function index()
    {
        $sites = Site::with('manager')->get();
        return response()->json(['success' => true, 'message' => 'Success', 'data' => SiteResource::collection($sites)], 200);
    }

    public function show(Site $site)
    {
        return response()->json(['success' => true, 'message' => 'Success', 'data' => new SiteResource($site->load('manager'))], 200);
    }

    public function store(StoreSiteRequest $request)
    {
        $data = $request->validated();

        $site = Site::create($data);
        return response()->json(['success' => true, 'message' => 'Site created', 'data' => new SiteResource($site)], 201);
    }

    public function update(UpdateSiteRequest $request, Site $site)
    {
        $data = $request->validated();

        $site->update($data);
        return response()->json(['success' => true, 'message' => 'Site updated', 'data' => new SiteResource($site)], 200);
    }

    public function destroy(Site $site)
    {
        $site->delete();
        return response()->json(['success' => true, 'message' => 'Site deleted', 'data' => null], 200);
    }
}
