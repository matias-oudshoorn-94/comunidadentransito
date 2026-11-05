<?php

declare(strict_types=1);

use App\Controllers\Admin;
use App\Controllers\AuthController;
use App\Controllers\CheckoutController;
use App\Controllers\ContentController;
use App\Controllers\HomeController;
use App\Controllers\MemberController;
use App\Controllers\PageController;
use App\Controllers\WebhookController;
use App\Core\Router;

/** @var Router $router */

// A · Sitio público
$router->get('/', [HomeController::class, 'index']);
$router->get('/la-comunidad', [PageController::class, 'community']);
$router->get('/membresias', [PageController::class, 'memberships']);
$router->get('/libros', [PageController::class, 'books']);
$router->get('/sobre-silvina', [PageController::class, 'about']);
$router->get('/contacto', [PageController::class, 'contact']);
$router->post('/contacto', [PageController::class, 'sendContact']);

// B · Contenidos para vos
$router->get('/contenidos', [ContentController::class, 'index']);
$router->get('/contenidos/runas-de-letras', [ContentController::class, 'runes']);
$router->post('/contenidos/runas-de-letras/sacar', [ContentController::class, 'drawRune']);
$router->post('/contenidos/tecniquitas/regalo', [ContentController::class, 'gift']);
$router->get('/contenidos/{section}', [ContentController::class, 'section']);
$router->get('/contenidos/{section}/{entry}', [ContentController::class, 'entry']);
$router->get('/contenidos/{section}/{entry}/agendar', [ContentController::class, 'calendar']);

// C · Compra y acceso
$router->get('/ingresar', [AuthController::class, 'loginForm'], ['guest']);
$router->post('/ingresar', [AuthController::class, 'login'], ['guest']);
$router->get('/registro', [AuthController::class, 'registerForm'], ['guest']);
$router->post('/registro', [AuthController::class, 'register'], ['guest']);
$router->post('/salir', [AuthController::class, 'logout'], ['auth']);

$router->get('/compra', [CheckoutController::class, 'show']);
$router->post('/compra', [CheckoutController::class, 'start']);
$router->get('/pago/retorno/{gateway}', [CheckoutController::class, 'gatewayReturn'], ['auth']);
$router->get('/pago/simulado/{id}', [CheckoutController::class, 'simulated'], ['auth']);
$router->post('/pago/simulado/{id}', [CheckoutController::class, 'confirmSimulated'], ['auth']);
$router->get('/bienvenida', [CheckoutController::class, 'welcome'], ['auth']);

// D · Miembros
$router->group(['middleware' => ['auth']], function (Router $r) {
    $r->get('/mi-comunidad', [MemberController::class, 'dashboard']);
    $r->get('/mi-cuenta', [MemberController::class, 'account']);
    $r->post('/mi-cuenta/datos', [MemberController::class, 'updateProfile']);
    $r->post('/mi-cuenta/clave', [MemberController::class, 'updatePassword']);
    $r->post('/mi-cuenta/renovacion', [MemberController::class, 'stopRenewal']);
    $r->get('/mi-cuenta/comprobante/{id}', [MemberController::class, 'receipt']);
});

// Notificaciones de pago (sin CSRF: se validan con la firma de cada pasarela)
$router->post('/webhooks/mercadopago', [WebhookController::class, 'mercadopago'], ['nocsrf']);
$router->post('/webhooks/paypal', [WebhookController::class, 'paypal'], ['nocsrf']);

// Administración
$router->group(['prefix' => '/admin', 'middleware' => ['admin']], function (Router $r) {
    $r->get('', [Admin\DashboardController::class, 'index']);

    $r->get('/usuarios', [Admin\UserController::class, 'index']);
    $r->get('/usuarios/{id}', [Admin\UserController::class, 'show']);
    $r->post('/usuarios/{id}', [Admin\UserController::class, 'update']);
    $r->post('/usuarios/{id}/membresia', [Admin\UserController::class, 'assignMembership']);

    $r->get('/membresias', [Admin\MembershipController::class, 'index']);
    $r->post('/membresias/{id}', [Admin\MembershipController::class, 'update']);

    $r->get('/pagos', [Admin\PaymentController::class, 'index']);
    $r->post('/pagos/{id}', [Admin\PaymentController::class, 'update']);

    $r->get('/calendario', [Admin\CalendarController::class, 'index']);

    $r->get('/planes', [Admin\PlanController::class, 'index']);
    $r->post('/planes/{id}', [Admin\PlanController::class, 'update']);

    $r->get('/secciones', [Admin\SectionController::class, 'index']);
    $r->post('/secciones/{id}', [Admin\SectionController::class, 'update']);

    $r->get('/contenidos', [Admin\EntryController::class, 'index']);
    $r->get('/contenidos/nueva', [Admin\EntryController::class, 'create']);
    $r->post('/contenidos', [Admin\EntryController::class, 'store']);
    $r->get('/contenidos/{id}', [Admin\EntryController::class, 'edit']);
    $r->post('/contenidos/{id}', [Admin\EntryController::class, 'update']);
    $r->post('/contenidos/{id}/borrar', [Admin\EntryController::class, 'destroy']);

    $r->get('/mensajes', [Admin\MessageController::class, 'index']);
    $r->post('/mensajes/{id}/leido', [Admin\MessageController::class, 'markRead']);
});
