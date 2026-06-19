<?php

namespace App\Models;

use Illuminate\Support\Collection;

class InventoryLocation
{
    public static function all(): Collection
    {
        $branches = Branch::select('id', 'name', \DB::raw("'Branch' as type"), 'location as address', 'created_at', 'updated_at')
            ->get()
            ->map(fn($b) => [
                'id' => 'branch_' . $b->id,
                'location_type' => Branch::class,
                'location_id' => $b->id,
                'name' => $b->name,
                'type' => 'branch',
                'address' => $b->address,
                'created_at' => $b->created_at,
                'updated_at' => $b->updated_at,
            ]);

        $sites = Site::select('id', 'name', 'status as type', 'address', 'created_at', 'updated_at')
            ->get()
            ->map(fn($s) => [
                'id' => 'site_' . $s->id,
                'location_type' => Site::class,
                'location_id' => $s->id,
                'name' => $s->name,
                'type' => 'site',
                'address' => $s->address,
                'created_at' => $s->created_at,
                'updated_at' => $s->updated_at,
            ]);

        return $branches->concat($sites)->values();
    }
}
