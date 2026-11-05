<?php

return [
    'driver'      => env('DB_DRIVER', 'sqlite'),
    'sqlite_path' => env('DB_SQLITE_PATH', 'storage/database.sqlite'),
    'host'        => env('DB_HOST', '127.0.0.1'),
    'port'        => env('DB_PORT', '3306'),
    'database'    => env('DB_DATABASE', 'comunidad_en_transito'),
    'username'    => env('DB_USERNAME', 'root'),
    'password'    => (string) env('DB_PASSWORD', ''),
];
