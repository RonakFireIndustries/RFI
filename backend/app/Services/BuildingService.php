<?php

namespace App\Services;

use App\Models\Building;

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
        return Building::create($data);
    }

    public function updateBuilding(Building $building, array $data): Building
    {
        $building->update($data);
        return $building;
    }

    public function deleteBuilding(Building $building): void
    {
        $building->delete();
    }
}
