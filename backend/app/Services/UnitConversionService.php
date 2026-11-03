<?php

namespace App\Services;

use App\Models\Unit;
use App\Models\UnitConversion;

/**
 * Converts quantities between units.
 *
 * The conversion table is treated as a graph: a direct row gives both
 * directions (the reverse is the reciprocal), and two or more rows can be
 * chained (KG -> G -> ...). The product's own unit is the base every stock
 * figure is kept in.
 *
 * `convert()` returns null when no path exists. Callers that must not fail a
 * save use `convertOrSame()`, which treats an unknown path as 1:1 — the
 * behaviour chosen for this app so a missing conversion row never blocks a
 * stock movement.
 */
class UnitConversionService
{
    public function convert(float $quantity, int $fromUnitId, int $toUnitId): ?float
    {
        if ($fromUnitId === $toUnitId) {
            return $quantity;
        }

        $path = $this->getConversionPath($fromUnitId, $toUnitId);

        if ($path === null) {
            return null;
        }

        return $quantity * array_product(array_column($path, 'factor'));
    }

    /**
     * The multiplier that turns a quantity in `$fromUnitId` into `$toUnitId`.
     * Missing units or a missing path are treated as 1:1.
     */
    public function factor(?int $fromUnitId, ?int $toUnitId): float
    {
        if (! $fromUnitId || ! $toUnitId || $fromUnitId === $toUnitId) {
            return 1.0;
        }

        $path = $this->getConversionPath($fromUnitId, $toUnitId);

        if ($path === null) {
            return 1.0;
        }

        return (float) array_product(array_column($path, 'factor'));
    }

    /**
     * Convert, but never fail: a unit missing from the conversion table is
     * assumed to be the same unit.
     */
    public function convertOrSame(float $quantity, ?int $fromUnitId, ?int $toUnitId): float
    {
        return $quantity * $this->factor($fromUnitId, $toUnitId);
    }

    /**
     * The steps from one unit to another, or null when they cannot be reached.
     *
     * Breadth-first so the shortest chain wins; each step is
     * `['from' => id, 'to' => id, 'factor' => multiplier]`.
     *
     * @return array<int, array{from: int, to: int, factor: float}>|null
     */
    public function getConversionPath(int $fromUnitId, int $toUnitId): ?array
    {
        if ($fromUnitId === $toUnitId) {
            return [['from' => $fromUnitId, 'to' => $toUnitId, 'factor' => 1.0]];
        }

        $fromUnit = Unit::find($fromUnitId);
        $toUnit = Unit::find($toUnitId);

        if (! $fromUnit || ! $toUnit) {
            return null;
        }

        // Different dimensions (weight vs length) never convert, even if a
        // stray row links them.
        if ($fromUnit->type && $toUnit->type && $fromUnit->type !== $toUnit->type) {
            return null;
        }

        $adjacency = [];
        foreach (UnitConversion::query()->get(['from_unit_id', 'to_unit_id', 'conversion_factor']) as $row) {
            $factor = (float) $row->conversion_factor;

            if ($factor == 0.0) {
                continue;
            }

            $adjacency[(int) $row->from_unit_id][(int) $row->to_unit_id] = $factor;
            $adjacency[(int) $row->to_unit_id][(int) $row->from_unit_id] = 1 / $factor;
        }

        if (! isset($adjacency[$fromUnitId])) {
            return null;
        }

        $queue = [$fromUnitId];
        $visited = [$fromUnitId => true];
        // node => [previous node, factor from previous node]
        $previous = [];

        while ($queue !== []) {
            $node = array_shift($queue);

            if ($node === $toUnitId) {
                break;
            }

            foreach ($adjacency[$node] ?? [] as $next => $factor) {
                if (isset($visited[$next])) {
                    continue;
                }

                $visited[$next] = true;
                $previous[$next] = [$node, $factor];
                $queue[] = $next;
            }
        }

        if (! isset($visited[$toUnitId])) {
            return null;
        }

        $steps = [];
        $node = $toUnitId;

        while ($node !== $fromUnitId) {
            [$prev, $factor] = $previous[$node];
            array_unshift($steps, ['from' => $prev, 'to' => $node, 'factor' => (float) $factor]);
            $node = $prev;
        }

        return $steps;
    }
}
