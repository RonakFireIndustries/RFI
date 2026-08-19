<?php

namespace App\Services;

use App\Models\Building;
use Illuminate\Support\Facades\DB;

class BuildingService
{
    public function listBuildings(array $filters = [], $perPage = 15)
    {
        $query = Building::with('site');

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('address', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('city', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('plot_no', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['site_id'])) {
            $query->where('site_id', $filters['site_id']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function createBuilding(array $data): Building
    {
        $wingsData = $data['wings'] ?? null;
        unset($data['wings']);

        return DB::transaction(function () use ($data, $wingsData) {
            $building = Building::create($data);

            if (!empty($wingsData)) {
                $building->wings()->createMany($wingsData);
            }

            return $building;
        });
    }

    public function updateBuilding(Building $building, array $data): Building
    {
        $wingsData = $data['wings'] ?? null;
        unset($data['wings']);

        return DB::transaction(function () use ($building, $data, $wingsData) {
            $building->update($data);

            if ($wingsData !== null) {
                $existingWingIds = collect($wingsData)
                    ->pluck('id')
                    ->filter()
                    ->map(fn ($id) => (int) $id)
                    ->toArray();

                $building->wings()
                    ->whereNotIn('id', $existingWingIds)
                    ->delete();

                foreach ($wingsData as $wingDatum) {
                    if (!empty($wingDatum['id'])) {
                        $building->wings()
                            ->where('id', $wingDatum['id'])
                            ->update(collect($wingDatum)->except('id')->toArray());
                    } else {
                        $building->wings()->create($wingDatum);
                    }
                }
            }

            return $building;
        });
    }

    public function deleteBuilding(Building $building): void
    {
        $building->delete();
    }
}
