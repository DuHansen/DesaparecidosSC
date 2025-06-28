<?php
return [
    'paths' => [
        'api/*',
        'logout',
        'login',
        'sanctum/csrf-cookie',
        'geolocation/*'
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'],


    'allowed_headers' => ['*'],

    'supports_credentials' => true,
];
