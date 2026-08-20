<?php
header('Content-Type: application/json');
if (function_exists('opcache_reset')) {
    echo json_encode(['opcache' => opcache_reset() ? 'reset_ok' : 'reset_failed']);
} else {
    echo json_encode(['opcache' => 'not_available']);
}
