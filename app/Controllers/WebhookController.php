<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\ErrorHandler;
use App\Core\HttpException;
use App\Services\Payments\MercadoPagoGateway;
use App\Services\Payments\PayPalGateway;
use Throwable;

/** Notificaciones de las pasarelas. No llevan CSRF: se validan con la firma de cada pasarela. */
final class WebhookController extends Controller
{
    public function mercadopago(): void
    {
        $this->handle(fn () => MercadoPagoGateway::isConfigured() ? (new MercadoPagoGateway())->handleWebhook($this->request) : null);
    }

    public function paypal(): void
    {
        $this->handle(fn () => PayPalGateway::isConfigured() ? (new PayPalGateway())->handleWebhook($this->request) : null);
    }

    private function handle(callable $callback): never
    {
        try {
            $callback();
        } catch (HttpException $e) {
            $this->json(['error' => $e->getMessage()], $e->status);
        } catch (Throwable $e) {
            // 500 → la pasarela reintenta más tarde
            ErrorHandler::log($e);
            $this->json(['error' => 'retry'], 500);
        }
        $this->json(['ok' => true]);
    }
}
