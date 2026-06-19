<?php

namespace App\Services;

use App\Models\Unit;
use App\Models\UnitConversion;

class UnitConversionService
{
    public function convert(float $quantity, int $fromUnitId, int $toUnitId): ?float
    {
        if ($fromUnitId === $toUnitId) {
            return $quantity;
        }

        $conversion = UnitConversion::where('from_unit_id', $fromUnitId)
            ->where('to_unit_id', $toUnitId)
            ->first();

        if ($conversion) {
            return $quantity * $conversion->conversion_factor;
        }

        $reverse = UnitConversion::where('from_unit_id', $toUnitId)
            ->where('to_unit_id', $fromUnitId)
            ->first();

        if ($reverse) {
            return $quantity / $reverse->conversion_factor;
        }

        return null;
    }

    public function getConversionPath(int $fromUnitId, int $toUnitId): ?array
    {
        if ($fromUnitId === $toUnitId) {
            return [['from' => $fromUnitId, 'to' => $toUnitId, 'factor' => 1]];
        }

        $direct = UnitConversion::where('from_unit_id', $fromUnitId)
            ->where('to_unit_id', $toUnitId)
            ->first();

        if ($direct) {
            return [['from' => $fromUnitId, 'to' => $toUnitId, 'factor' => $direct->conversion_factor]];
        }

        $reverse = UnitConversion::where('from_unit_id', $toUnitId)
            ->where('to_unit_id', $fromUnitId)
            ->first();

        if ($reverse) {
            return [['from' => $fromUnitId, 'to' => $toUnitId, 'factor' => 1 / $reverse->conversion_factor]];
        }

        $fromUnit = Unit::find($fromUnitId);
        $toUnit = Unit::find($toUnitId);
        if ($fromUnit && $toUnit && $fromUnit->type !== $toUnit->type) {
            return null;
        }

        $intermediate1 = UnitConversion::where('from_unit_id', $fromUnitId)
            ->orWhere('to_unit_id', $fromUnitId)
            ->first();

        if ($intermediate1) {
            $midId = $intermediate1->from_unit_id === $fromUnitId
                ? $intermediate1->to_unit_id
                : $intermediate1->from_unit_id;

            $intermediate2 = UnitConversion::where(function ($q) use ($midId, $toUnitId) {
                $q->where('from_unit_id', $midId)->where('to_unit_id', $toUnitId);
            })->orWhere(function ($q) use ($midId, $toUnitId) {
                $q->where('from_unit_id', $toUnitId)->where('to_unit_id', $midId);
            })->first();

            if ($intermediate2) {
                $factor1 = $intermediate1->from_unit_id === $fromUnitId
                    ? $intermediate1->conversion_factor
                    : 1 / $intermediate1->conversion_factor;

                $factor2 = $intermediate2->from_unit_id === $midId
                    ? $intermediate2->conversion_factor
                    : 1 / $intermediate2->conversion_factor;

                return [
                    ['from' => $fromUnitId, 'to' => $midId, 'factor' => $factor1],
                    ['from' => $midId, 'to' => $toUnitId, 'factor' => $factor2],
                ];
            }
        }

        return null;
    }
}
