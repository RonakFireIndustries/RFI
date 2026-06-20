<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;

class InventoryLocationController extends Controller
{
    public function index(Request $request)
    {
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

        return response()->json($sites->values());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:site',
            'address' => 'nullable|string|max:500',
        ]);

        $site = Site::create(['name' => $data['name'], 'address' => $data['address'] ?? null]);
        return response()->json(['id' => 'site_' . $site->id, 'type' => 'site', 'name' => $site->name], 201);
    }
}
