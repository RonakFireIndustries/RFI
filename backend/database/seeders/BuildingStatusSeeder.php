<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BuildingStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['name' => 'Existing Client', 'color' => '#DC2626', 'marker_color' => 'red', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Quotation Submitted', 'color' => '#EAB308', 'marker_color' => 'yellow', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Active Project', 'color' => '#16A34A', 'marker_color' => 'green', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'AMC Client', 'color' => '#2563EB', 'marker_color' => 'blue', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Prospect', 'color' => '#374151', 'marker_color' => 'gray', 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('building_statuses')->insert($statuses);
    }
}
