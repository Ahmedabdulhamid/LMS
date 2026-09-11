<?php

return [
    'broadcasting' => [
        'echo' => [
            'broadcaster' => 'pusher',
            'key' => env('PUSHER_APP_KEY'),
            'cluster' => env('PUSHER_APP_CLUSTER', 'mt1'),
            'forceTLS' => true,
            'authEndpoint' => '/broadcasting/auth',
        ],
    ],
];
