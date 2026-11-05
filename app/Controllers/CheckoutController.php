<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\ErrorHandler;
use App\Models\Entry;
use App\Models\Membership;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\User;
use App\Services\Mailer;
use App\Services\MembershipService;
use App\Services\Payments\GatewayFactory;
use App\Services\Payments\SimulatedGateway;
use Throwable;

final class CheckoutController extends Controller
{
    /** C1 · Compra */
    public function show(): void
    {
        $plan = Plan::findBy('slug', (string) $this->request->query('plan', 'inmersion-mensual'));
        if (!$plan || !(int) $plan['is_active']) {
            $this->redirect('/membresias');
        }
        $this->view('checkout/show', [
            'title'      => 'Compra',
            'headerMode' => 'checkout',
            'plan'       => $plan,
            'gateways'   => GatewayFactory::available(),
        ]);
    }

    public function start(): void
    {
        $plan = Plan::find($this->request->int('plan_id'));
        $gateways = GatewayFactory::available();
        $gatewayName = $this->request->string('gateway', 'mercadopago');
        if (!$plan || !(int) $plan['is_active'] || !isset($gateways[$gatewayName])) {
            $this->back('Elegí un plan y un medio de pago.');
        }

        // Quien no tiene cuenta la crea en el mismo paso. La dirección es para la carta Slow Mood.
        $user = Auth::user();
        $rules = [
            'address'     => 'required|max:200',
            'city'        => 'required|max:120',
            'postal_code' => 'nullable|max:20',
        ];
        if (!$user) {
            $rules += [
                'name'     => 'required|max:120',
                'email'    => 'required|email|max:190|unique:users,email',
                'password' => 'required|min:8|max:200',
            ];
        }
        $data = $this->validate($rules);
        if (!$user) {
            $id = User::register($data['name'], $data['email'], (string) $this->request->input('password'));
            Auth::login(User::find($id));
        }
        User::update((int) Auth::id(), [
            'address'     => $data['address'],
            'city'        => $data['city'],
            'postal_code' => $data['postal_code'] ?? null,
        ]);
        Auth::refresh();
        $user = Auth::user();

        $gateway = GatewayFactory::make($gatewayName);
        $ids = MembershipService::startCheckout($user, $plan, $gatewayName, $gateway->currency());
        $payment = Payment::find($ids['payment_id']);

        try {
            $url = $gateway->createCheckout($payment, $plan, $user);
        } catch (Throwable $e) {
            ErrorHandler::log($e);
            MembershipService::rejectPayment((int) $payment['id']);
            $this->redirect('/compra?plan=' . $plan['slug'], 'No pudimos conectar con ' . GatewayFactory::LABELS[$gatewayName] . '. Probá de nuevo en unos minutos.');
        }
        redirect($url);
    }

    /** Vuelta desde Mercado Pago / PayPal */
    public function gatewayReturn(string $gateway): void
    {
        $payment = Payment::find($this->request->int('pago'));
        $user = Auth::user();
        if (!$payment || $payment['gateway'] !== $gateway || !$user || (int) $payment['user_id'] !== (int) $user['id']) {
            $this->redirect('/mi-cuenta');
        }
        try {
            $status = GatewayFactory::make($gateway)->handleReturn($this->request, $payment);
        } catch (Throwable $e) {
            ErrorHandler::log($e);
            $status = 'pending';
        }
        $this->finish((int) $payment['id'], $status);
    }

    /** Pantalla de pago simulado (solo en local y sin credenciales) */
    public function simulated(string $id): void
    {
        $payment = $this->ownPendingSimulated((int) $id);
        $this->view('checkout/simulated', [
            'title'   => 'Pago simulado',
            'payment' => $payment,
            'plan'    => Plan::find((int) $payment['plan_id']),
            'label'   => GatewayFactory::LABELS[$payment['gateway']] ?? $payment['gateway'],
        ]);
    }

    public function confirmSimulated(string $id): void
    {
        $payment = $this->ownPendingSimulated((int) $id);
        if ($this->request->string('result') === 'approved') {
            MembershipService::approvePayment((int) $payment['id'], 'sim-' . $payment['id'] . '-' . bin2hex(random_bytes(3)));
            $this->finish((int) $payment['id'], 'approved');
        }
        MembershipService::rejectPayment((int) $payment['id']);
        $this->finish((int) $payment['id'], 'rejected');
    }

    /** C3 · Bienvenida */
    public function welcome(): void
    {
        $user = Auth::user();
        $payment = Payment::find($this->request->int('pago'));
        if ($payment && (int) $payment['user_id'] !== (int) $user['id']) {
            $payment = null;
        }
        $sectionIds = array_column(\App\Models\Section::allWithTiers(), 'id');
        $this->view('checkout/welcome', [
            'title'      => 'Bienvenida',
            'payment'    => $payment,
            'membership' => Membership::currentFor((int) $user['id']),
            'event'      => Entry::upcomingEvents($sectionIds, 1)[0] ?? null,
        ]);
    }

    private function finish(int $paymentId, string $status): never
    {
        $payment = Payment::find($paymentId);
        if ($status === 'approved') {
            $plan = Plan::find((int) $payment['plan_id']);
            $user = Auth::user();
            Mailer::send(
                $user['email'],
                'Te damos la bienvenida a ' . config('app.name'),
                "¡Hola, {$user['name']}!\n\nTu membresía {$plan['name']} ya está activa.\nEntrá a tu espacio: " . absolute_url('/mi-comunidad') . "\n\nCon cariño,\nSilvina",
            );
            $this->redirect('/bienvenida?pago=' . $paymentId);
        }
        if ($status === 'rejected') {
            $plan = Plan::find((int) $payment['plan_id']);
            $this->redirect('/compra?plan=' . ($plan['slug'] ?? ''), 'El pago no se completó. Podés intentarlo de nuevo cuando quieras.');
        }
        $this->redirect('/bienvenida?pago=' . $paymentId);
    }

    private function ownPendingSimulated(int $id): array
    {
        $payment = Payment::find($id);
        $ok = $payment
            && (int) $payment['user_id'] === Auth::id()
            && $payment['status'] === 'pending'
            && GatewayFactory::make($payment['gateway']) instanceof SimulatedGateway;
        return $ok ? $payment : $this->abort(404);
    }
}
