<?php

namespace App\Models;

use Illuminate\Support\Collection;

class InventoryLocation
{
    public static function all(): Collection
    {
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

        return $sites->values();
    }
}
