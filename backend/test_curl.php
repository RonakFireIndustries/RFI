<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('email', 'admin@erp.com')->first();
$token = $user->createToken('test')->plainTextToken;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://127.0.0.1:8000/api/products");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . $token,
    "Accept: application/json"
]);

$result = curl_exec($ch);
curl_close($ch);

echo "API Response: " . $result . "\n";
