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

        if ($request->filled('building_id')) {
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
     * Bulk-create extinguishers for a building (identified by name).
     *
     * Body: { building_name, count, items: [{ label, installation_date, next_refill_date }] }
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('extinguishers.create');

        $validated = $request->validate([
            'building_name' => ['required', 'string', 'max:255'],
            'count' => ['required', 'integer', 'min:1', 'max:500'],
            'items' => ['nullable', 'array'],
            'items.*.label' => ['nullable', 'string', 'max:255'],
            'items.*.location' => ['nullable', 'string', 'max:255'],
            'items.*.type' => ['nullable', 'string', 'max:255'],
            'items.*.capacity' => ['nullable', 'integer', 'min:0'],
            'items.*.installation_date' => ['nullable', 'date'],
            'items.*.next_refill_date' => ['nullable', 'date'],
            'items.*.year_of_manufacturing' => ['nullable', 'integer'],
            'items.*.remark' => ['nullable', 'string', 'max:1000'],
        ]);

        $building = Building::firstOrCreate(
            ['name' => trim($validated['building_name'])],
            ['name' => trim($validated['building_name'])]
        );

        $count = (int) $validated['count'];
        $items = $validated['items'] ?? [];

        $created = [];
        for ($i = 0; $i < $count; $i++) {
            $row = $items[$i] ?? [];
            $created[] = $building->fireSystems()->create([
                'system_type' => FireSystem::TYPE_EXTINGUISHER,
                'sub_type' => 'Fire Extinguisher',
                'quantity' => 1,
                'type' => $row['type'] ?? null,
                'capacity' => $row['capacity'] ?? null,
                'label' => $row['label'] ?? null,
                'location' => $row['location'] ?? null,
                'installation_date' => $row['installation_date'] ?? null,
                'next_refill_date' => $row['next_refill_date'] ?? null,
                'year_of_manufacturing' => $row['year_of_manufacturing'] ?? null,
                'remark' => $row['remark'] ?? null,
            ]);
        }

        return $this->success("{$count} extinguisher(s) added to {$building->name}", [
            'building' => ['id' => $building->id, 'name' => $building->name],
            'extinguishers' => array_map(fn ($e) => $this->present($e), $created),
        ], [], 201);
    }

    /**
     * Update a single extinguisher (label / dates).
     */
    public function update(Request $request, FireSystem $fireSystem): JsonResponse
    {
        $this->authorize('extinguishers.update');

        $validated = $request->validate([
            'label' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:255'],
            'capacity' => ['nullable', 'integer', 'min:0'],
            'installation_date' => ['nullable', 'date'],
            'next_refill_date' => ['nullable', 'date'],
            'year_of_manufacturing' => ['nullable', 'integer'],
            'remark' => ['nullable', 'string', 'max:1000'],
        ]);

        $data = [
            'system_type' => FireSystem::TYPE_EXTINGUISHER,
            'sub_type' => 'Fire Extinguisher',
            'quantity' => 1,
            'label' => $validated['label'] ?? $fireSystem->label,
            'capacity' => $validated['capacity'] ?? $fireSystem->capacity,
            'installation_date' => !empty($validated['installation_date']) ? $validated['installation_date'] : null,
            'next_refill_date' => !empty($validated['next_refill_date']) ? $validated['next_refill_date'] : null,
            'location' => $validated['location'] ?? $fireSystem->location,
            'type' => $validated['type'] ?? $fireSystem->type,
            'year_of_manufacturing' => $validated['year_of_manufacturing'] ?? $fireSystem->year_of_manufacturing,
            'remark' => $validated['remark'] ?? $fireSystem->remark,
        ];

        if (array_key_exists('location', $validated) && $validated['location'] === '') {
            $data['location'] = null;
        }
        if (array_key_exists('type', $validated) && $validated['type'] === '') {
            $data['type'] = null;
        }
        if (array_key_exists('remark', $validated) && $validated['remark'] === '') {
            $data['remark'] = null;
        }
        if (array_key_exists('year_of_manufacturing', $validated)
            && ($validated['year_of_manufacturing'] === '' || $validated['year_of_manufacturing'] === null
                || $validated['year_of_manufacturing'] === '0')) {
            $data['year_of_manufacturing'] = null;
        }

        $fireSystem->update($data);

        return $this->success('Extinguisher updated', [
            'extinguisher' => $this->present($fireSystem->fresh()),
        ]);
    }

    /**
     * Delete a single extinguisher.
     */
    public function destroy(FireSystem $fireSystem): JsonResponse
    {
        $this->authorize('extinguishers.delete');

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
            'location' => $e->location,
            'type' => $e->type,
            'capacity' => $e->capacity !== null ? (int) $e->capacity : null,
            'installation_date' => $e->installation_date?->toDateString(),
            'next_refill_date' => $e->next_refill_date?->toDateString(),
            'year_of_manufacturing' => $e->year_of_manufacturing !== null ? (int) $e->year_of_manufacturing : null,
            'remark' => $e->remark,
            'quantity' => $e->quantity,
        ];
    }
}
