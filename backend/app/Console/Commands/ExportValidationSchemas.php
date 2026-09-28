<?php

namespace App\Console\Commands;

use App\Support\ValidationSchemas;
use Illuminate\Console\Command;

class ExportValidationSchemas extends Command
{
    protected $signature = 'validation:export {--path= : Absolute path of the JSON file to write}';
    protected $description = 'Export backend validation schemas as JSON for the frontend';

    public function handle(): int
    {
        $default = base_path('../frontend/src/config/validationSchemas.json');
        $path = $this->option('path') ?: $default;

        $json = json_encode(ValidationSchemas::export(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents($path, $json . PHP_EOL);

        $this->info('Validation schemas exported to: ' . $path);
        return Command::SUCCESS;
    }
}