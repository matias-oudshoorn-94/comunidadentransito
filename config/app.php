<?php

return [
    'name'        => env('APP_NAME', 'Comunidad en Tránsito'),
    'env'         => env('APP_ENV', 'production'),
    'debug'       => (bool) env('APP_DEBUG', false),
    'url'         => rtrim((string) env('APP_URL', 'http://localhost:8000'), '/'),
    'timezone'    => env('APP_TIMEZONE', 'America/Argentina/Buenos_Aires'),
    'mail_driver' => env('MAIL_DRIVER', 'log'),
    'mail_from'   => env('MAIL_FROM', 'hola@comunidadentransito.com'),
];
