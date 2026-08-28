<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\FireSystem;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FireExtinguisherController extends Controller
{
    use ApiResponse;

    /**
     * List all fire extinguishers across buildings (optionally filtered).
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('extinguishers.view');

        $query = FireSystem::extinguishers()->with('building:id,name');

        $routeBuilding = $request->route('building');
        if ($routeBuilding) {
            $routeBuildingId = is_object($routeBuilding) ? $routeBuilding->id : (int) $routeBuilding;
            $query->where('building_id', $routeBuildingId);
        } elseif ($request->filled('building_id')) {
            $query->where('building_id', $request->integer('building_id'));
        }

        if ($request->filled('due')) {
            // only extinguishers whose next refill is due on/before today
            $query->whereNotNull('next_refill_date')
                  ->whereDate('next_refill_date', '<=', now()->toDateString());
        }

        $extinguishers = $query->orderBy('id', 'desc')->paginate($request->integer('per_page', 50));

        return $this->success('Extinguishers retrieved', [
            'extinguishers' => $extinguishers->items(),
            'pagination' => [
                'total' => $extinguishers->total(),
                'per_page' => $extinguishers->perPage(),
                'current_page' => $extinguishers->currentPage(),
                'last_page' => $extinguishers->lastPage(),
            ],
        ]);
    }

    /**
     * Bulk-create extinguishers for a building.
     *
     * Body: { count, items: [{ label, installation_date, next_refill_date }] }
     */
    public function store(Request $request, Building $building): JsonResponse
    {
        $this->authorize('extinguishers.create');

        $validated = $request->validate([
            'count' => ['required', 'integer', 'min:1', 'max:500'],
            'items' => ['nullable', 'array'],
            'items.*.label' => ['nullable', 'string', 'max:255'],
            'items.*.installation_date' => ['nullable', 'date'],
            'items.*.next_refill_date' => ['nullable', 'date'],
        ]);

        $count = (int) $validated['count'];
        $items = $validated['items'] ?? [];

        $created = [];
        for ($i = 0; $i < $count; $i++) {
            $row = $items[$i] ?? [];
            $seq = $i + 1;
            $created[] = $building->fireSystems()->create([
                'system_type' => FireSystem::TYPE_EXTINGUISHER,
                'sub_type' => 'Fire Extinguisher',
                'quantity' => 1,
                'label' => $row['label'] ?? null,
                'installation_date' => $row['installation_date'] ?? null,
                'next_refill_date' => $row['next_refill_date'] ?? null,
            ]);
        }

        return $this->success("{$count} extinguisher(s) added", [
            'extinguishers' => array_map(fn ($e) => $this->present($e), $created),
        ], [], 201);
    }

    /**
     * Update a single extinguisher (label / dates / quantity).
     */
    public function update(Request $request, Building $building, FireSystem $fireSystem): JsonResponse
    {
        $this->authorize('extinguishers.update');

        if ($fireSystem->building_id !== $building->id) {
            return $this->error('Extinguisher does not belong to this building.', [], 422);
        }

        $validated = $request->validate([
            'label' => ['nullable', 'string', 'max:255'],
            'installation_date' => ['nullable', 'date'],
            'next_refill_date' => ['nullable', 'date'],
        ]);

        $fireSystem->update([
            'system_type' => FireSystem::TYPE_EXTINGUISHER,
            'sub_type' => 'Fire Extinguisher',
            'quantity' => 1,
            'label' => $validated['label'] ?? $fireSystem->label,
            'installation_date' => !empty($validated['installation_date']) ? $validated['installation_date'] : null,
            'next_refill_date' => !empty($validated['next_refill_date']) ? $validated['next_refill_date'] : null,
        ]);

        return $this->success('Extinguisher updated', [
            'extinguisher' => $this->present($fireSystem->fresh()),
        ]);
    }

    /**
     * Delete a single extinguisher.
     */
    public function destroy(Building $building, FireSystem $fireSystem): JsonResponse
    {
        $this->authorize('extinguishers.delete');

        if ($fireSystem->building_id !== $building->id) {
            return $this->error('Extinguisher does not belong to this building.', [], 422);
        }

        $fireSystem->delete();

        return $this->success('Extinguisher deleted');
    }

    protected function present(FireSystem $e): array
    {
        return [
            'id' => $e->id,
            'building_id' => $e->building_id,
            'building' => [
                'id' => $e->building_id,
                'name' => $e->building?->name ?? null,
            ],
            'label' => $e->label,
            'installation_date' => $e->installation_date?->toDateString(),
            'next_refill_date' => $e->next_refill_date?->toDateString(),
            'quantity' => $e->quantity,
        ];
    }
}
