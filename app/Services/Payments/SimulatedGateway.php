<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Core\Request;
use App\Models\Payment;

/** Solo para desarrollo: reemplaza la pasarela por una pantalla con "Aprobar" / "Rechazar". */
final class SimulatedGateway implements PaymentGateway
{
    public function __construct(private readonly string $name, private readonly string $currency)
    {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function createCheckout(array $payment, array $plan, array $user): string
    {
        Payment::update((int) $payment['id'], ['note' => 'Pago simulado (desarrollo)']);
        return '/pago/simulado/' . $payment['id'];
    }

    public function handleReturn(Request $request, array $payment): string
    {
        return $payment['status'];
    }

    public function handleWebhook(Request $request): void
    {
    }
}
