<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Unit;
use App\Models\UnitConversion;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['name' => 'Piece', 'code' => 'PCS', 'type' => 'quantity'],
            ['name' => 'Box', 'code' => 'BOX', 'type' => 'quantity'],
            ['name' => 'Kilogram', 'code' => 'KG', 'type' => 'weight'],
            ['name' => 'Gram', 'code' => 'G', 'type' => 'weight'],
            ['name' => 'Ton', 'code' => 'TON', 'type' => 'weight'],
            ['name' => 'Liter', 'code' => 'L', 'type' => 'volume'],
            ['name' => 'Milliliter', 'code' => 'ML', 'type' => 'volume'],
            ['name' => 'Meter', 'code' => 'M', 'type' => 'length'],
            ['name' => 'Centimeter', 'code' => 'CM', 'type' => 'length'],
            ['name' => 'Square Feet', 'code' => 'SQFT', 'type' => 'area'],
            ['name' => 'Packet', 'code' => 'PKT', 'type' => 'quantity'],
            ['name' => 'Dozen', 'code' => 'DOZ', 'type' => 'quantity'],
            ['name' => 'Pair', 'code' => 'PR', 'type' => 'quantity'],
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate(
                ['code' => $unit['code']],
                $unit
            );
        }

        $conversions = [
            ['from' => 'KG', 'to' => 'G', 'factor' => 1000],
            ['from' => 'G', 'to' => 'KG', 'factor' => 0.001],
            ['from' => 'TON', 'to' => 'KG', 'factor' => 1000],
            ['from' => 'KG', 'to' => 'TON', 'factor' => 0.001],
            ['from' => 'BOX', 'to' => 'PCS', 'factor' => 12],
            ['from' => 'PCS', 'to' => 'BOX', 'factor' => 1 / 12],
            ['from' => 'L', 'to' => 'ML', 'factor' => 1000],
            ['from' => 'ML', 'to' => 'L', 'factor' => 0.001],
            ['from' => 'M', 'to' => 'CM', 'factor' => 100],
            ['from' => 'CM', 'to' => 'M', 'factor' => 0.01],
            ['from' => 'DOZ', 'to' => 'PCS', 'factor' => 12],
            ['from' => 'PCS', 'to' => 'DOZ', 'factor' => 1 / 12],
        ];

        foreach ($conversions as $conv) {
            $from = Unit::where('code', $conv['from'])->first();
            $to = Unit::where('code', $conv['to'])->first();
            if ($from && $to) {
                UnitConversion::firstOrCreate(
                    ['from_unit_id' => $from->id, 'to_unit_id' => $to->id],
                    ['conversion_factor' => $conv['factor']]
                );
            }
        }
    }
}
