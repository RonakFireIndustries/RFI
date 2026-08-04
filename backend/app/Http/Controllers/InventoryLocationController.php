<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class InventoryLocationController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('inventory.locations.view');

        $sites = Site::select(
            'id', 'name', 'code', 'address', 'city', 'state', 'pincode',
            'contact_person', 'phone', 'email', 'status', 'created_at', 'updated_at'
        )
            ->get()
            ->map(fn (Site $site) => $this->locationPayload($site));

        return response()->json($sites->values());
    }

    public function show(Request $request, string $location): JsonResponse
    {
        $this->authorize('inventory.locations.view');

        $site = $this->resolveSite($location);

        $employees = $site->employeeSites()
            ->with(['employee.designation', 'employee.department'])
            ->get()
            ->map(fn ($es) => [
                'id' => $es->employee->id,
                'name' => $es->employee->full_name ?: $es->employee->user?->name,
                'emp_id' => $es->employee->emp_id,
                'designation' => $es->employee->designation?->name,
                'department' => $es->employee->department?->name,
                'role' => $es->role,
                'assigned_at' => $es->assigned_at?->toDateString(),
            ])
            ->values();

        $products = $site->stock()
            ->with('product')
            ->get()
            ->map(fn ($stock) => [
                'id' => $stock->id,
                'product_id' => $stock->product_id,
                'product_name' => $stock->product?->name,
                'sku' => $stock->product?->sku,
                'quantity' => (float) $stock->quantity,
                'available_quantity' => (float) $stock->available_quantity,
                'reserved_quantity' => (float) $stock->reserved_quantity,
            ])
            ->values();

        $data = $this->locationPayload($site);
        $data['employees'] = $employees;
        $data['products'] = $products;

        return response()->json($data);
    }

    public function store(Request $request)
    {
        $this->authorize('inventory.locations.create');

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:site',
            'address' => 'nullable|string|max:500',
        ]);

        $site = Site::create(['name' => $data['name'], 'address' => $data['address'] ?? null]);

        return response()->json($this->locationPayload($site), 201);
    }

    public function update(Request $request, string $location): JsonResponse
    {
        $this->authorize('inventory.locations.edit');

        $site = $this->resolveSite($location);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'status' => 'nullable|in:active,inactive',
        ]);

        $site->update($data);

        return response()->json($this->locationPayload($site->refresh()));
    }

    public function destroy(Request $request, string $location): JsonResponse
    {
        $this->authorize('inventory.locations.delete');

        $this->resolveSite($location)->delete();

        return response()->json(null, 204);
    }

    private function resolveSite(string $location): Site
    {
        $id = str_starts_with($location, 'site_') ? (int) substr($location, 5) : (int) $location;
        return Site::findOrFail($id);
    }

    private function locationPayload(Site $site): array
    {
        return [
            'id' => 'site_' . $site->id,
            'location_type' => 'App\\Models\\Site',
            'location_id' => $site->id,
            'type' => 'site',
            'name' => $site->name,
            'code' => $site->code,
            'address' => $site->address,
            'city' => $site->city,
            'state' => $site->state,
            'pincode' => $site->pincode,
            'contact_person' => $site->contact_person,
            'phone' => $site->phone,
            'email' => $site->email,
            'status' => strtolower((string) $site->status),
            'stock_count' => $site->stock()->count(),
            'created_at' => $site->created_at,
            'updated_at' => $site->updated_at,
        ];
    }
}
