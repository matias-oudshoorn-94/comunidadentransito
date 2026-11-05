<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Core\HttpException;
use App\Core\Request;
use App\Models\Membership;
use App\Models\Payment;
use App\Services\HttpClient;
use App\Services\MembershipService;
use RuntimeException;

/**
 * Mercado Pago con suscripciones (preapproval): renovación automática y baja desde Mi cuenta.
 * Docs: https://www.mercadopago.com.ar/developers/es/reference/subscriptions/_preapproval/post
 *
 * Webhooks a configurar en el panel de MP → https://TU-DOMINIO/webhooks/mercadopago
 * con los eventos "Planes y suscripciones" (subscription_preapproval y subscription_authorized_payment).
 */
final class MercadoPagoGateway implements PaymentGateway
{
    private array $config;

    public function __construct()
    {
        $this->config = config('payments.mercadopago');
    }

    public static function isConfigured(): bool
    {
        return config('payments.mercadopago.access_token') !== '';
    }

    public function name(): string
    {
        return 'mercadopago';
    }

    public function currency(): string
    {
        return (string) $this->config['currency'];
    }

    public function createCheckout(array $payment, array $plan, array $user): string
    {
        $response = $this->api('POST', '/preapproval', [
            'reason'             => config('app.name') . ' · ' . $plan['name'],
            'external_reference' => 'pago-' . $payment['id'],
            'payer_email'        => $user['email'],
            'auto_recurring'     => [
                'frequency'          => (int) $plan['months'],
                'frequency_type'     => 'months',
                'transaction_amount' => (float) $payment['amount'],
                'currency_id'        => $payment['currency'],
            ],
            'back_url' => absolute_url('/pago/retorno/mercadopago?pago=' . $payment['id']),
            'status'   => 'pending',
        ]);

        Payment::update((int) $payment['id'], ['gateway_reference' => $response['id']]);
        Membership::update((int) $payment['membership_id'], ['gateway_subscription_id' => $response['id']]);

        return (string) $response['init_point'];
    }

    public function handleReturn(Request $request, array $payment): string
    {
        if (!$payment['gateway_reference']) {
            return 'pending';
        }
        $preapproval = $this->api('GET', '/preapproval/' . rawurlencode((string) $payment['gateway_reference']));
        return $this->syncPreapproval($preapproval);
    }

    public function handleWebhook(Request $request): void
    {
        $body = $request->json();
        $type = (string) ($request->query('type') ?? $body['type'] ?? $request->query('topic') ?? '');
        // PHP convierte "data.id" de la URL en "data_id"
        $dataId = (string) ($request->query('data_id') ?? $body['data']['id'] ?? $request->query('id') ?? '');
        if ($dataId === '') {
            return;
        }
        $this->verifySignature($request, $dataId);

        switch ($type) {
            case 'subscription_preapproval':
                $this->syncPreapproval($this->api('GET', '/preapproval/' . rawurlencode($dataId)));
                break;

            case 'subscription_authorized_payment':
                $charge = $this->api('GET', '/authorized_payments/' . rawurlencode($dataId));
                $status = $charge['payment']['status'] ?? null;
                $membership = Membership::findBy('gateway_subscription_id', (string) ($charge['preapproval_id'] ?? ''));
                if ($status === 'approved' && $membership) {
                    MembershipService::recordRenewal(
                        (int) $membership['id'],
                        'mp-' . $charge['payment']['id'],
                        (float) ($charge['transaction_amount'] ?? 0),
                        (string) ($charge['currency_id'] ?? 'ARS'),
                        $charge,
                    );
                }
                break;
        }
    }

    /**
     * Sincroniza el estado de la suscripción con nuestra membresía.
     * "authorized" = la persona aceptó la suscripción y MP cobra el primer período:
     * activamos de inmediato para que pueda entrar sin esperar al webhook.
     */
    private function syncPreapproval(array $preapproval): string
    {
        $paymentId = (int) str_replace('pago-', '', (string) ($preapproval['external_reference'] ?? ''));
        $payment = Payment::find($paymentId);
        if (!$payment || $payment['gateway_reference'] !== ($preapproval['id'] ?? null)) {
            return 'pending';
        }

        switch ($preapproval['status'] ?? '') {
            case 'authorized':
                MembershipService::approvePayment($paymentId, null, $preapproval);
                return 'approved';

            case 'cancelled':
            case 'paused':
                if ($payment['status'] === 'pending') {
                    MembershipService::rejectPayment($paymentId, $preapproval);
                    return 'rejected';
                }
                if ($payment['membership_id']) {
                    MembershipService::stopRenewal((int) $payment['membership_id']);
                }
                return $payment['status'];

            default:
                return 'pending';
        }
    }

    /** Cancela la suscripción en MP (baja desde Mi cuenta). */
    public function cancelSubscription(string $preapprovalId): void
    {
        $this->api('PUT', '/preapproval/' . rawurlencode($preapprovalId), ['status' => 'cancelled']);
    }

    /**
     * Valida la firma x-signature con la clave secreta del webhook.
     * https://www.mercadopago.com.ar/developers/es/docs/your-integrations/notifications/webhooks
     */
    private function verifySignature(Request $request, string $dataId): void
    {
        $secret = (string) $this->config['webhook_secret'];
        if ($secret === '') {
            return;
        }
        $parts = [];
        foreach (explode(',', (string) $request->header('X-Signature')) as $part) {
            [$k, $v] = array_pad(array_map('trim', explode('=', $part, 2)), 2, '');
            $parts[$k] = $v;
        }
        $id = ctype_alnum($dataId) ? strtolower($dataId) : $dataId;
        $manifest = "id:{$id};request-id:" . $request->header('X-Request-Id') . ";ts:" . ($parts['ts'] ?? '') . ";";
        $expected = hash_hmac('sha256', $manifest, $secret);
        if (!isset($parts['v1']) || !hash_equals($expected, $parts['v1'])) {
            throw new HttpException(401, 'Firma inválida');
        }
    }

    private function api(string $method, string $path, ?array $body = null): array
    {
        $response = HttpClient::request($method, $this->config['api'] . $path, [
            'Authorization' => 'Bearer ' . $this->config['access_token'],
        ], $body);
        if ($response['status'] >= 400) {
            throw new RuntimeException("Mercado Pago respondió {$response['status']}: {$response['raw']}");
        }
        return $response['body'];
    }
}
