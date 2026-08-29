<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Unit;
use Illuminate\Console\Command;

class BackfillProductUnits extends Command
{
    protected $signature = 'products:backfill-units {--dry-run : Report what would change without writing}';

    protected $description = 'Assign a unit to every product that has none, inferred from the product name/dimension. Creates standard units as needed.';

    /**
     * Baseline units (code is the unique key).
     */
    protected array $baselineUnits = [
        ['name' => 'Nos', 'code' => 'NOS', 'type' => 'quantity'],
        ['name' => 'Piece', 'code' => 'PCS', 'type' => 'quantity'],
        ['name' => 'Set', 'code' => 'SET', 'type' => 'quantity'],
        ['name' => 'Pair', 'code' => 'PR', 'type' => 'quantity'],
        ['name' => 'Kit', 'code' => 'KIT', 'type' => 'quantity'],
        ['name' => 'Roll', 'code' => 'RL', 'type' => 'quantity'],
        ['name' => 'Box', 'code' => 'BOX', 'type' => 'quantity'],
        ['name' => 'Carton', 'code' => 'CTN', 'type' => 'quantity'],
        ['name' => 'Dozen', 'code' => 'DZ', 'type' => 'quantity'],
        ['name' => 'Meter', 'code' => 'M', 'type' => 'length'],
        ['name' => 'Sq Meter', 'code' => 'SQM', 'type' => 'area'],
        ['name' => 'Sq Ft', 'code' => 'SQF', 'type' => 'area'],
        ['name' => 'Kg', 'code' => 'KG', 'type' => 'weight'],
        ['name' => 'Gm', 'code' => 'G', 'type' => 'weight'],
        ['name' => 'Litre', 'code' => 'LTR', 'type' => 'volume'],
        ['name' => 'Bag', 'code' => 'BAG', 'type' => 'quantity'],
    ];

    /**
     * Rules evaluated in order; the first hit wins.
     * Each: [unitCode, array of substrings to search for].
     */
    protected array $rules = [
        ['KG', ['kilogram', 'kg ', ' kg', ' kg.', 'kg.']],
        ['G', ['gram']],
        ['LTR', ['litre', 'liter', ' ltr', 'ltr ']],
        ['SQF', ['sq.ft', 'sq ft', 'sqf', 'sq. ft', 'square foot']],
        ['SQM', ['sq.m', 'sq m', 'sqm', 'sq.m.', 'square meter', 'square metre']],
        ['M', ['meter', 'metre', ' mtr', 'mtr ', 'm ']],
        ['PR', ['pair', 'pairs']],
        ['SET', [' set', 'set of', ' complete set']],
        ['KIT', ['kit', ' kits']],
        ['RL', ['roll', 'coil']],
        ['BOX', ['box', 'boxes', 'pack of']],
        ['DZ', ['dozen']],
        ['BAG', ['bag', 'bags', 'sack']],
        ['CTN', ['carton']],
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        // Ensure baseline units exist so we have something to assign.
        $unitsByCode = [];
        foreach ($this->baselineUnits as $u) {
            $unit = Unit::firstOrCreate(['code' => $u['code']], $u);
            $unitsByCode[$u['code']] = $unit->id;
        }

        // Default unit when nothing matches.
        $defaultUnitId = $unitsByCode['NOS'];

        $products = Product::whereNull('unit_id')->get();
        if ($products->isEmpty()) {
            $this->info('No products are missing a unit.');
            return Command::SUCCESS;
        }

        $counts = [];
        $updated = 0;

        foreach ($products as $product) {
            $hay = strtolower(trim(($product->name ?? '') . ' ' . ($product->dimension ?? '')));

            $unitId = null;
            foreach ($this->rules as [$code, $needles]) {
                foreach ($needles as $needle) {
                    if (str_contains($hay, $needle)) {
                        $unitId = $unitsByCode[$code];
                        break 2;
                    }
                }
            }
            if (!$unitId) {
                $unitId = $defaultUnitId;
            }

            $unitName = Unit::find($unitId)?->name ?? '-';
            $counts[$unitName] = ($counts[$unitName] ?? 0) + 1;

            if (!$dryRun) {
                $product->unit_id = $unitId;
                $product->save();
            }
            $updated++;
        }

        $this->info(($dryRun ? '[DRY RUN] ' : '') . "Assigned units to {$updated} product(s):");
        foreach ($counts as $name => $n) {
            $this->line("  - {$name}: {$n}");
        }

        return Command::SUCCESS;
    }
}
