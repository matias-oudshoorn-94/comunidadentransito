<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Core\Request;

interface PaymentGateway
{
    public function name(): string;

    /** Moneda en la que cobra: ARS (Mercado Pago) o USD (PayPal). */
    public function currency(): string;

    /** Prepara el cobro y devuelve la URL a la que hay que mandar a la persona. */
    public function createCheckout(array $payment, array $plan, array $user): string;

    /**
     * Procesa la vuelta desde la pasarela, consultando su API (nunca confiando solo en la URL).
     * Devuelve 'approved' | 'pending' | 'rejected'.
     */
    public function handleReturn(Request $request, array $payment): string;

    /** Notificaciones asíncronas de la pasarela (pagos, renovaciones, bajas). */
    public function handleWebhook(Request $request): void;
}
