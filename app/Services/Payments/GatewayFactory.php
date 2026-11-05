<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Core\HttpException;

final class GatewayFactory
{
    public const LABELS = [
        'mercadopago' => 'Mercado Pago',
        'paypal'      => 'PayPal',
        'manual'      => 'Manual',
    ];

    public static function make(string $name): PaymentGateway
    {
        $real = match ($name) {
            'mercadopago' => MercadoPagoGateway::isConfigured() ? new MercadoPagoGateway() : null,
            'paypal'      => PayPalGateway::isConfigured() ? new PayPalGateway() : null,
            default       => throw new HttpException(404),
        };
        if ($real !== null) {
            return $real;
        }
        if (self::simulationAllowed()) {
            return new SimulatedGateway($name, $name === 'paypal' ? 'USD' : 'ARS');
        }
        throw new HttpException(503, 'Este medio de pago no está disponible por el momento.');
    }

    /** Medios de pago que se muestran en la página de compra. */
    public static function available(): array
    {
        $list = [];
        if (MercadoPagoGateway::isConfigured() || self::simulationAllowed()) {
            $list['mercadopago'] = ['label' => 'Mercado Pago', 'currency' => 'ARS', 'hint' => 'En pesos, con renovación automática'];
        }
        if (PayPalGateway::isConfigured() || self::simulationAllowed()) {
            $list['paypal'] = ['label' => 'PayPal', 'currency' => 'USD', 'hint' => 'En dólares, desde cualquier país'];
        }
        return $list;
    }

    /** Sin credenciales y en local, se puede probar todo el circuito con un pago simulado. */
    public static function simulationAllowed(): bool
    {
        return config('app.env') === 'local';
    }

    public static function isSimulated(string $name): bool
    {
        return self::make($name) instanceof SimulatedGateway;
    }
}
