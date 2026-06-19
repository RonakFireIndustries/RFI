<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Unit;
use App\Models\Category;
use Illuminate\Console\Command;

class ImportProducts extends Command
{
    protected $signature = 'import:products
        {file : Path to the CSV file}
        {--name-col=0 : Column index (0-based) for product name}
        {--unit-col=1 : Column index for unit code (e.g. KG, PCS, BOX)}
        {--sku-col= : Column index for SKU (optional)}
        {--category-col= : Column index for category name (optional)}
        {--has-header : Skip the first row as header}
        {--delimiter=, : CSV delimiter}';

    protected $description = 'Import products from a CSV file';

    public function handle(): int
    {
        $path = $this->argument('file');

        if (!file_exists($path)) {
            $this->error("File not found: {$path}");
            return Command::FAILURE;
        }

        $handle = fopen($path, 'r');
        if (!$handle) {
            $this->error("Could not open file: {$path}");
            return Command::FAILURE;
        }

        $nameCol = (int) $this->option('name-col');
        $unitCol = (int) $this->option('unit-col');
        $skuCol = $this->option('sku-col') !== null ? (int) $this->option('sku-col') : null;
        $catCol = $this->option('category-col') !== null ? (int) $this->option('category-col') : null;
        $delimiter = $this->option('delimiter');
        $hasHeader = $this->option('has-header');

        $units = Unit::pluck('id', 'code');

        $created = 0;
        $skipped = 0;
        $errors = [];
        $rowNum = 0;

        if ($hasHeader) {
            fgetcsv($handle, 0, $delimiter);
            $rowNum++;
        }

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rowNum++;
            $name = trim($row[$nameCol] ?? '');

            if (empty($name)) {
                $skipped++;
                continue;
            }

            $unitCode = strtoupper(trim($row[$unitCol] ?? ''));
            $unitId = $units[$unitCode] ?? null;

            if (!$unitId) {
                $errors[] = "Row {$rowNum}: Unknown unit '{$unitCode}' for product '{$name}'";
                $skipped++;
                continue;
            }

            $sku = $skuCol !== null ? trim($row[$skuCol] ?? '') : null;

            $categoryId = null;
            if ($catCol !== null) {
                $catName = trim($row[$catCol] ?? '');
                if (!empty($catName)) {
                    $category = Category::firstOrCreate(['name' => $catName]);
                    $categoryId = $category->id;
                }
            }

            $data = [
                'name' => $name,
                'unit_id' => $unitId,
                'sku' => $sku ?? 'IMP-' . str_pad($rowNum, 5, '0', STR_PAD_LEFT),
                'product_code' => $sku ?? null,
                'category_id' => $categoryId,
                'status' => 'active',
            ];

            try {
                Product::create($data);
                $created++;
                $this->line("  ✓ {$name}");
            } catch (\Exception $e) {
                $errors[] = "Row {$rowNum}: {$name} — {$e->getMessage()}";
                $skipped++;
            }
        }

        fclose($handle);

        $this->newLine();
        $this->info("Created: {$created}, Skipped: {$skipped}");

        if (!empty($errors)) {
            $this->newLine();
            $this->warn('Errors:');
            foreach ($errors as $err) {
                $this->line("  {$err}");
            }
        }

        return Command::SUCCESS;
    }
}
