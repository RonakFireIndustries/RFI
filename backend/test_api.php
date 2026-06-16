<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create('/api/products', 'GET');
// simulate admin login
$user = App\Models\User::where('email', 'admin@erp.com')->first();
$app['auth']->guard('sanctum')->setUser($user);

$response = $kernel->handle($request);
echo $response->getContent();
