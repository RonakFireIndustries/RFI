<?php

namespace App\Services;

use App\Models\Building;
use Illuminate\Support\Facades\DB;

class BuildingService
{
    public function listBuildings(array $filters = [], $perPage = 15)
    {
        $query = Building::with('site', 'fireSystemsConfig');

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
        $fireSystemsData = $data['fire_systems'] ?? null;
        unset($data['wings'], $data['fire_systems']);

        return DB::transaction(function () use ($data, $wingsData, $fireSystemsData) {
            $building = Building::create($data);

            if (!empty($fireSystemsData)) {
                foreach ($fireSystemsData as $fs) {
                    $building->fireSystemsConfig()->create([
                        'system_type' => $fs['system_type'],
                        'sub_type' => $fs['sub_type'] ?? null,
                        'other_details' => $fs['other_details'] ?? null,
                    ]);
                }
            }

            if (!empty($wingsData)) {
                foreach ($wingsData as $wingDatum) {
                    $floorsData = $wingDatum['floors_data'] ?? null;
                    unset($wingDatum['floors_data']);

                    $wing = $building->wings()->create($wingDatum);

                    if (!empty($floorsData)) {
                        foreach ($floorsData as $floorDatum) {
                            $flatsData = $floorDatum['flats_data'] ?? null;
                            unset($floorDatum['flats_data']);

                            $floor = $wing->buildingFloors()->create([
                                'building_id' => $building->id,
                                'name' => $floorDatum['name'],
                                'floor_number' => $floorDatum['floor_number'] ?? null,
                                'type' => $floorDatum['type'] ?? null,
                            ]);

                            if (!empty($flatsData)) {
                                foreach ($flatsData as $flatDatum) {
                                    $floor->buildingFlats()->create([
                                        'building_id' => $building->id,
                                        'wing_id' => $wing->id,
                                        'name' => $flatDatum['name'],
                                        'flat_number' => $flatDatum['flat_number'] ?? null,
                                        'bhk_type' => $flatDatum['bhk_type'] ?? null,
                                        'area' => $flatDatum['area'] ?? null,
                                    ]);
                                }
                            }
                        }
                    }
                }
            }

            return $building;
        });
    }

    public function updateBuilding(Building $building, array $data): Building
    {
        $wingsData = $data['wings'] ?? null;
        $fireSystemsData = $data['fire_systems'] ?? null;
        unset($data['wings'], $data['fire_systems']);

        return DB::transaction(function () use ($building, $data, $wingsData, $fireSystemsData) {
            $building->update($data);

            if ($fireSystemsData !== null) {
                $existingIds = collect($fireSystemsData)
                    ->pluck('id')
                    ->filter()
                    ->map(fn ($id) => (int) $id)
                    ->toArray();

                $building->fireSystemsConfig()
                    ->whereNotIn('id', $existingIds)
                    ->delete();

                foreach ($fireSystemsData as $fs) {
                    $payload = [
                        'system_type' => $fs['system_type'],
                        'sub_type' => $fs['sub_type'] ?? null,
                        'other_details' => $fs['other_details'] ?? null,
                    ];

                    if (!empty($fs['id'])) {
                        $building->fireSystemsConfig()
                            ->where('id', (int) $fs['id'])
                            ->update($payload);
                    } else {
                        $building->fireSystemsConfig()->create($payload);
                    }
                }
            }

            if ($wingsData !== null) {
                $existingWingIds = collect($wingsData)
                    ->pluck('id')
                    ->filter()
                    ->map(fn ($id) => (int) $id)
                    ->toArray();

                // Delete wings not in submitted list (cascades floors + flats)
                $building->wings()
                    ->whereNotIn('id', $existingWingIds)
                    ->delete();

                foreach ($wingsData as $wingDatum) {
                    $floorsData = $wingDatum['floors_data'] ?? null;
                    unset($wingDatum['floors_data']);

                    if (!empty($wingDatum['id'])) {
                        // Update existing wing
                        $wingId = $wingDatum['id'];
                        $building->wings()
                            ->where('id', $wingId)
                            ->update(collect($wingDatum)->except('id')->toArray());
                        $wing = $building->wings()->find($wingId);
                    } else {
                        // Create new wing
                        $wing = $building->wings()->create($wingDatum);
                    }

                    // Handle nested floors
                    if ($wing && $floorsData !== null) {
                        $existingFloorIds = collect($floorsData)
                            ->pluck('id')
                            ->filter()
                            ->map(fn ($id) => (int) $id)
                            ->toArray();

                        $wing->buildingFloors()
                            ->whereNotIn('id', $existingFloorIds)
                            ->delete();

                        foreach ($floorsData as $floorDatum) {
                            $flatsData = $floorDatum['flats_data'] ?? null;
                            unset($floorDatum['flats_data']);

                            $floorPayload = [
                                'building_id' => $building->id,
                                'wing_id' => $wing->id,
                                'name' => $floorDatum['name'],
                                'floor_number' => $floorDatum['floor_number'] ?? null,
                                'type' => $floorDatum['type'] ?? null,
                            ];

                            if (!empty($floorDatum['id'])) {
                                $floorId = $floorDatum['id'];
                                $wing->buildingFloors()
                                    ->where('id', $floorId)
                                    ->update($floorPayload);
                                $floor = $wing->buildingFloors()->find($floorId);
                            } else {
                                $floor = $wing->buildingFloors()->create($floorPayload);
                            }

                            // Handle nested flats
                            if ($floor && $flatsData !== null) {
                                $existingFlatIds = collect($flatsData)
                                    ->pluck('id')
                                    ->filter()
                                    ->map(fn ($id) => (int) $id)
                                    ->toArray();

                                $floor->buildingFlats()
                                    ->whereNotIn('id', $existingFlatIds)
                                    ->delete();

                                foreach ($flatsData as $flatDatum) {
                                    $flatPayload = [
                                        'building_id' => $building->id,
                                        'wing_id' => $wing->id,
                                        'floor_id' => $floor->id,
                                        'name' => $flatDatum['name'],
                                        'flat_number' => $flatDatum['flat_number'] ?? null,
                                        'bhk_type' => $flatDatum['bhk_type'] ?? null,
                                        'area' => $flatDatum['area'] ?? null,
                                    ];

                                    if (!empty($flatDatum['id'])) {
                                        $floor->buildingFlats()
                                            ->where('id', $flatDatum['id'])
                                            ->update($flatPayload);
                                    } else {
                                        $floor->buildingFlats()->create($flatPayload);
                                    }
                                }
                            }
                        }
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
