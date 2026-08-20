<?php
header('Content-Type: application/json');

$result = ['opcache' => 'unknown'];

if (function_exists('opcache_reset')) {
    $result['opcache'] = opcache_reset() ? 'reset成功' : 'reset_failed';
} elseif (function_exists('opcache_invalidate')) {
    $result['opcache'] = 'invalidate_not_reset';
} else {
    $result['opcache'] = 'function_not_available';
}

$result['php_version'] = PHP_VERSION;
$result['extensions'] = array_filter(get_loaded_extensions(), fn($e) => str_contains($e, 'opcache'));

echo json_encode($result, JSON_PRETTY_PRINT);
