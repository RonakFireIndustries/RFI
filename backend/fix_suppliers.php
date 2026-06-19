<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$result = DB::select('SHOW CREATE TABLE suppliers');
echo "Current: " . json_encode($result[0]->{'Create Table'}) . "\n";

$keys = DB::select('SHOW KEYS FROM suppliers WHERE Key_name = ?', ['PRIMARY']);
if (empty($keys)) {
    DB::statement('ALTER TABLE suppliers ADD PRIMARY KEY (id)');
    echo "PRIMARY KEY added\n";
}

DB::statement('ALTER TABLE suppliers MODIFY id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT');
echo "AUTO_INCREMENT added\n";

$result2 = DB::select('SHOW CREATE TABLE suppliers');
echo "Final: " . json_encode($result2[0]->{'Create Table'}) . "\n";
