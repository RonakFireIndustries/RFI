<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function index()
    {
        $sites = Site::all();
        return response()->json(['status' => 'success', 'data' => $sites]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:sites',
            'client_details' => 'nullable|string',
            'status' => 'string|in:Active,Completed,On-Hold',
            'site_manager_id' => 'nullable|exists:employees,id'
        ]);

        $site = Site::create($request->all());

        return response()->json(['status' => 'success', 'data' => $site], 201);
    }

    public function show(string $id)
    {
        $site = Site::findOrFail($id);
        return response()->json(['status' => 'success', 'data' => $site]);
    }

    public function update(Request $request, string $id)
    {
        $site = Site::findOrFail($id);
        
        $request->validate([
            'name' => 'string|unique:sites,name,'.$site->id,
            'status' => 'string|in:Active,Completed,On-Hold',
        ]);

        $site->update($request->all());

        return response()->json(['status' => 'success', 'data' => $site]);
    }

    public function destroy(string $id)
    {
        Site::destroy($id);
        return response()->json(['status' => 'success', 'message' => 'Site deleted successfully']);
    }
}
