<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\ErrorHandler;
use App\Core\Session;
use App\Models\Entry;
use App\Models\Membership;
use App\Models\Payment;
use App\Models\Rune;
use App\Models\Section;
use App\Models\User;
use App\Services\AccessService;
use App\Services\MembershipService;
use App\Services\Payments\MercadoPagoGateway;
use Throwable;

final class MemberController extends Controller
{
    /** D1 · Mi Comunidad */
    public function dashboard(): void
    {
        $user = Auth::user();
        $sections = array_values(array_filter(
            Section::allWithTiers(),
            fn (array $s) => AccessService::canAccessSection($user, $s),
        ));
        $ids = array_column($sections, 'id');

        // Lo nuevo del mes: lo último de cada sección abierta (una entrega por sección)
        $latest = [];
        foreach (Entry::latestIn($ids, 30) as $e) {
            if (isset($latest[$e['section_id']]) || $e['kind'] === 'encuentro') {
                continue;
            }
            $latest[$e['section_id']] = $e + ['url' => "/contenidos/{$e['section_slug']}/{$e['slug']}"];
        }
        // Orden de Figma: la entrega destacada y después Lugares y Capituleando
        $priority = ['pispeando-arte', 'lugares', 'capituleando'];
        $latest = array_values($latest);
        usort($latest, function ($a, $b) use ($priority) {
            $pa = array_search($a['section_slug'], $priority, true);
            $pb = array_search($b['section_slug'], $priority, true);
            return ($pa === false ? 99 : $pa) <=> ($pb === false ? 99 : $pb);
        });

        $this->view('member/dashboard', [
            'title'      => 'Mi Comunidad',
            'user'       => $user,
            'membership' => Membership::currentFor((int) $user['id']),
            'latest'     => array_slice($latest, 0, 3),
            'event'      => Entry::upcomingEvents($ids, 1)[0] ?? null,
            'rune'       => Rune::drawnToday((int) $user['id']),
            'canRunes'   => in_array('runas-de-letras', array_column($sections, 'slug'), true),
        ]);
    }

    /** D2 · Mi cuenta */
    public function account(): void
    {
        $user = Auth::user();
        $current = Membership::currentFor((int) $user['id']);
        if ($current) {
            $current['last_amount'] = \App\Core\Database::value(
                "SELECT amount FROM payments WHERE membership_id = ? AND status = 'approved' ORDER BY id DESC LIMIT 1",
                [$current['id']],
            );
        }
        $this->view('member/account', [
            'title'    => 'Mi cuenta',
            'user'     => $user,
            'current'  => $current,
            'payments' => Payment::forUser((int) $user['id']),
        ]);
    }

    /** Comprobante imprimible de un pago aprobado */
    public function receipt(string $id): void
    {
        $user = Auth::user();
        $payment = \App\Core\Database::one(
            "SELECT pay.*, p.name AS plan_name FROM payments pay LEFT JOIN plans p ON p.id = pay.plan_id WHERE pay.id = ? AND pay.user_id = ? AND pay.status = 'approved'",
            [(int) $id, $user['id']],
        ) ?? $this->abort(404);
        $this->view('member/receipt', ['title' => 'Comprobante', 'payment' => $payment, 'user' => $user]);
    }

    /** Cada fila de "Tus datos" guarda solo su campo */
    public function updateProfile(): void
    {
        $user = Auth::user();
        $sent = array_intersect_key($this->request->all(), array_flip(['name', 'email', 'address', 'city', 'postal_code']));
        $rules = [
            'name'        => 'required|max:120',
            'email'       => 'required|email|max:190|unique:users,email,' . $user['id'],
            'address'     => 'required|max:200',
            'city'        => 'required|max:120',
            'postal_code' => 'nullable|max:20',
        ];
        $data = $this->validate(array_intersect_key($rules, $sent));
        if (isset($data['email'])) {
            $data['email'] = strtolower($data['email']);
        }
        if (array_key_exists('postal_code', $sent)) {
            $data['postal_code'] = $data['postal_code'] ?? null;
        }
        if ($data) {
            User::update((int) $user['id'], $data);
        }
        Auth::refresh();
        $this->redirect('/mi-cuenta', 'Guardamos tus datos.');
    }

    public function updatePassword(): void
    {
        $user = Auth::user();
        $this->validate([
            'current_password'      => 'required',
            'password'              => 'required|min:8|max:200',
            'password_confirmation' => 'required|same:password',
        ]);
        if (!password_verify((string) $this->request->input('current_password'), $user['password_hash'])) {
            Session::flash('errors', ['current_password' => ['La contraseña actual no coincide.']]);
            $this->back();
        }
        User::update((int) $user['id'], ['password_hash' => password_hash((string) $this->request->input('password'), PASSWORD_DEFAULT)]);
        Session::regenerate();
        $this->redirect('/mi-cuenta', 'Cambiaste tu contraseña.');
    }

    /** Baja de la renovación automática: sigue con acceso hasta el final del período */
    public function stopRenewal(): void
    {
        $user = Auth::user();
        $membership = Membership::find($this->request->int('membership_id'));
        if (!$membership || (int) $membership['user_id'] !== (int) $user['id']) {
            $this->abort(404);
        }
        if ($membership['gateway'] === 'mercadopago' && $membership['gateway_subscription_id'] && MercadoPagoGateway::isConfigured()) {
            try {
                (new MercadoPagoGateway())->cancelSubscription((string) $membership['gateway_subscription_id']);
            } catch (Throwable $e) {
                ErrorHandler::log($e);
                $this->back('No pudimos cancelar en Mercado Pago. Escribinos y lo resolvemos.');
            }
        }
        MembershipService::stopRenewal((int) $membership['id']);
        $this->redirect('/mi-cuenta', 'Listo, cancelaste la renovación. Seguís con acceso hasta el ' . fecha($membership['ends_at']) . '.');
    }
}
