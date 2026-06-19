<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Site;
use Illuminate\Http\Request;

class InventoryLocationController extends Controller
{
    public function index(Request $request)
    {
        $branches = Branch::select('id', 'name', 'location as address', 'created_at', 'updated_at')
            ->get()
            ->map(fn($b) => [
                'id' => 'branch_' . $b->id,
                'location_type' => 'App\\Models\\Branch',
                'location_id' => $b->id,
                'type' => 'branch',
                'name' => $b->name,
                'address' => $b->address,
                'stock_count' => $b->stock()->count(),
                'created_at' => $b->created_at,
                'updated_at' => $b->updated_at,
            ]);

        $sites = Site::select('id', 'name', 'address', 'created_at', 'updated_at')
            ->get()
            ->map(fn($s) => [
                'id' => 'site_' . $s->id,
                'location_type' => 'App\\Models\\Site',
                'location_id' => $s->id,
                'type' => 'site',
                'name' => $s->name,
                'address' => $s->address,
                'stock_count' => $s->stock()->count(),
                'created_at' => $s->created_at,
                'updated_at' => $s->updated_at,
            ]);

        return response()->json($branches->concat($sites)->values());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:branch,site',
            'address' => 'nullable|string|max:500',
        ]);

        if ($data['type'] === 'branch') {
            $branch = Branch::create(['name' => $data['name'], 'location' => $data['address'] ?? null]);
            return response()->json(['id' => 'branch_' . $branch->id, 'type' => 'branch', 'name' => $branch->name], 201);
        }

        $site = Site::create(['name' => $data['name'], 'address' => $data['address'] ?? null]);
        return response()->json(['id' => 'site_' . $site->id, 'type' => 'site', 'name' => $site->name], 201);
    }
}
