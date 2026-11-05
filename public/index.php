<?php

declare(strict_types=1);

// Con el servidor de desarrollo de PHP, los archivos estáticos se sirven directo
if (PHP_SAPI === 'cli-server') {
    $file = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (is_file($file)) {
        return false;
    }
}

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Request;
use App\Core\Router;
use App\Core\Session;

header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-Frame-Options: SAMEORIGIN');

Session::start();

$router = new Router();
require BASE_PATH . '/routes/web.php';
$router->dispatch(Request::capture());
