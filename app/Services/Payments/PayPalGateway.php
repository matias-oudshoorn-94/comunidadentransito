<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Core\HttpException;
use App\Core\Request;
use App\Models\Payment;
use App\Services\HttpClient;
use App\Services\MembershipService;
use RuntimeException;

/**
 * PayPal con Orders v2: un cobro por período (sin renovación automática).
 * Docs: https://developer.paypal.com/docs/api/orders/v2/
 *
 * Webhook a configurar en el panel de PayPal → https://TU-DOMINIO/webhooks/paypal
 * con los eventos PAYMENT.CAPTURE.COMPLETED y PAYMENT.CAPTURE.DENIED. Copiá su ID en PAYPAL_WEBHOOK_ID.
 */
final class PayPalGateway implements PaymentGateway
{
    private array $config;
    private ?string $token = null;

    public function __construct()
    {
        $this->config = config('payments.paypal');
    }

    public static function isConfigured(): bool
    {
        return config('payments.paypal.client_id') !== '' && config('payments.paypal.secret') !== '';
    }

    public function name(): string
    {
        return 'paypal';
    }

    public function currency(): string
    {
        return (string) $this->config['currency'];
    }

    public function createCheckout(array $payment, array $plan, array $user): string
    {
        $returnUrl = absolute_url('/pago/retorno/paypal?pago=' . $payment['id']);
        $order = $this->api('POST', '/v2/checkout/orders', [
            'intent'         => 'CAPTURE',
            'purchase_units' => [[
                'reference_id' => 'pago-' . $payment['id'],
                'custom_id'    => (string) $payment['id'],
                'description'  => config('app.name') . ' · ' . $plan['name'],
                'amount'       => [
                    'currency_code' => $payment['currency'],
                    'value'         => number_format((float) $payment['amount'], 2, '.', ''),
                ],
            ]],
            'payment_source' => ['paypal' => ['experience_context' => [
                'brand_name'          => config('app.name'),
                'locale'              => 'es-AR',
                'shipping_preference' => 'NO_SHIPPING',
                'user_action'         => 'PAY_NOW',
                'return_url'          => $returnUrl,
                'cancel_url'          => $returnUrl . '&cancelado=1',
            ]]],
        ], ['PayPal-Request-Id' => 'pago-' . $payment['id']]);

        Payment::update((int) $payment['id'], ['gateway_reference' => $order['id']]);

        foreach ($order['links'] ?? [] as $link) {
            if (in_array($link['rel'], ['payer-action', 'approve'], true)) {
                return (string) $link['href'];
            }
        }
        throw new RuntimeException('PayPal no devolvió el link de pago.');
    }

    public function handleReturn(Request $request, array $payment): string
    {
        if ($request->query('cancelado')) {
            MembershipService::rejectPayment((int) $payment['id']);
            return 'rejected';
        }
        $orderId = (string) $request->query('token', '');
        if ($orderId === '' || $orderId !== $payment['gateway_reference']) {
            return $payment['status'];
        }

        $response = $this->call('POST', '/v2/checkout/orders/' . rawurlencode($orderId) . '/capture', '{}', [
            'PayPal-Request-Id' => 'captura-' . $payment['id'],
        ]);
        $order = $response['body'];
        if ($response['status'] === 422) {
            // Ya estaba capturada (por ejemplo, la persona recargó la página)
            $order = $this->api('GET', '/v2/checkout/orders/' . rawurlencode($orderId));
        } elseif ($response['status'] >= 400) {
            throw new RuntimeException("PayPal respondió {$response['status']}: {$response['raw']}");
        }

        $capture = $order['purchase_units'][0]['payments']['captures'][0] ?? null;
        return match ($capture['status'] ?? null) {
            'COMPLETED' => $this->approve($payment, $capture, $order),
            'PENDING'   => 'pending',
            default     => $this->reject($payment, $order),
        };
    }

    public function handleWebhook(Request $request): void
    {
        $event = json_decode($request->raw(), true);
        if (!is_array($event)) {
            return;
        }
        $this->verifyWebhook($request, $event);

        $resource = $event['resource'] ?? [];
        $payment = Payment::find((int) ($resource['custom_id'] ?? 0));
        if (!$payment || $payment['gateway'] !== 'paypal') {
            return;
        }
        match ($event['event_type'] ?? '') {
            'PAYMENT.CAPTURE.COMPLETED' => $this->approve($payment, $resource, $event),
            'PAYMENT.CAPTURE.DENIED'    => $this->reject($payment, $event),
            default                     => null,
        };
    }

    private function approve(array $payment, array $capture, array $raw): string
    {
        MembershipService::approvePayment((int) $payment['id'], 'pp-' . $capture['id'], $raw);
        return 'approved';
    }

    private function reject(array $payment, array $raw): string
    {
        MembershipService::rejectPayment((int) $payment['id'], $raw);
        return 'rejected';
    }

    private function verifyWebhook(Request $request, array $event): void
    {
        if ($this->config['webhook_id'] === '') {
            throw new HttpException(401, 'Falta PAYPAL_WEBHOOK_ID');
        }
        $result = $this->api('POST', '/v1/notifications/verify-webhook-signature', [
            'auth_algo'         => $request->header('PayPal-Auth-Algo'),
            'cert_url'          => $request->header('PayPal-Cert-Url'),
            'transmission_id'   => $request->header('PayPal-Transmission-Id'),
            'transmission_sig'  => $request->header('PayPal-Transmission-Sig'),
            'transmission_time' => $request->header('PayPal-Transmission-Time'),
            'webhook_id'        => $this->config['webhook_id'],
            'webhook_event'     => $event,
        ]);
        if (($result['verification_status'] ?? '') !== 'SUCCESS') {
            throw new HttpException(401, 'Firma inválida');
        }
    }

    private function accessToken(): string
    {
        if ($this->token !== null) {
            return $this->token;
        }
        $response = HttpClient::request('POST', $this->config['api'] . '/v1/oauth2/token', [
            'Authorization' => 'Basic ' . base64_encode($this->config['client_id'] . ':' . $this->config['secret']),
            'Content-Type'  => 'application/x-www-form-urlencoded',
        ], 'grant_type=client_credentials');
        if ($response['status'] >= 400 || empty($response['body']['access_token'])) {
            throw new RuntimeException('No se pudo autenticar con PayPal: ' . $response['raw']);
        }
        return $this->token = (string) $response['body']['access_token'];
    }

    private function call(string $method, string $path, array|string|null $body = null, array $headers = []): array
    {
        $headers = ['Authorization' => 'Bearer ' . $this->accessToken()] + $headers;
        if (is_string($body)) {
            $headers['Content-Type'] = 'application/json';
        }
        return HttpClient::request($method, $this->config['api'] . $path, $headers, $body);
    }

    private function api(string $method, string $path, ?array $body = null, array $headers = []): array
    {
        $response = $this->call($method, $path, $body, $headers);
        if ($response['status'] >= 400) {
            throw new RuntimeException("PayPal respondió {$response['status']}: {$response['raw']}");
        }
        return $response['body'];
    }
}
