<?php
return [
'paths' => [
    'api/*',
    'logout',
    'login',
    'sanctum/csrf-cookie'
],
'allowed_methods' => ['*'],

'allowed_origins' => ['http://localhost', 'http://localhost:8080'],
 // <-- use a porta exata do frontend

'allowed_headers' => ['*'],

'supports_credentials' => true,


];
