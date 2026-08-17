<?php

return [

    // Flip this value between 'development' and 'production' to switch the whole backend.
    'active' => 'development',

    'environments' => [

        'development' => [
            'APP_ENV' => 'local',
            'APP_DEBUG' => true,
            'APP_URL' => 'http://localhost:8000',
            'FRONTEND_URL' => 'http://localhost:5173',

            'DB_HOST' => '127.0.0.1',
            'DB_PORT' => '3306',
            'DB_DATABASE' => 'erp_db',
            'DB_USERNAME' => 'root',
            'DB_PASSWORD' => '',

            'SESSION_DRIVER' => 'file',
            'CACHE_STORE' => 'file',
            'QUEUE_CONNECTION' => 'sync',

            'PUSHER_APP_ID' => '2179433',
            'PUSHER_APP_KEY' => 'c81c2da2537855f4f133',
            'PUSHER_APP_SECRET' => '8ca8526fb416cd008bf4',
            'PUSHER_APP_CLUSTER' => 'ap2',

            'SANCTUM_STATEFUL_DOMAINS' => 'localhost,localhost:5173,127.0.0.1,127.0.0.1:5173',
        ],

        'production' => [
            'APP_ENV' => 'production',
            'APP_DEBUG' => false,
            'APP_URL' => 'https://rfibackend.ronakfire.com',
            'FRONTEND_URL' => 'https://rfi.ronakfire.com',

            'DB_HOST' => '127.0.0.1',
            'DB_PORT' => '3306',
            'DB_DATABASE' => 'a17590e4_erp_db',
            'DB_USERNAME' => 'a17590e4_root',
            'DB_PASSWORD' => 'Predator@6565',

            'SESSION_DRIVER' => 'file',
            'CACHE_STORE' => 'file',
            'QUEUE_CONNECTION' => 'sync',

            'PUSHER_APP_ID' => '2179433',
            'PUSHER_APP_KEY' => 'c81c2da2537855f4f133',
            'PUSHER_APP_SECRET' => '8ca8526fb416cd008bf4',
            'PUSHER_APP_CLUSTER' => 'ap2',

            'SANCTUM_STATEFUL_DOMAINS' => 'localhost,localhost:5173,127.0.0.1,rfibackend.ronakfire.com,rfi.ronakfire.com',
        ],

    ],

];
