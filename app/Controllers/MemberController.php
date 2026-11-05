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

        $this->view('member/dashboard', [
            'title'      => 'Mi Comunidad',
            'user'       => $user,
            'membership' => Membership::currentFor((int) $user['id']),
            'sections'   => $sections,
            'latest'     => Entry::latestIn($ids, 6),
            'events'     => Entry::upcomingEvents($ids, 3),
            'rune'       => Rune::drawnToday((int) $user['id']),
            'canRunes'   => in_array('runas-de-letras', array_column($sections, 'slug'), true),
        ]);
    }

    /** D2 · Mi cuenta */
    public function account(): void
    {
        $user = Auth::user();
        $this->view('member/account', [
            'title'       => 'Mi cuenta',
            'user'        => $user,
            'current'     => Membership::currentFor((int) $user['id']),
            'memberships' => Membership::forUser((int) $user['id']),
            'payments'    => Payment::forUser((int) $user['id']),
        ]);
    }

    public function updateProfile(): void
    {
        $user = Auth::user();
        $data = $this->validate([
            'name'    => 'required|max:120',
            'email'   => 'required|email|max:190|unique:users,email,' . $user['id'],
            'phone'   => 'nullable|max:40',
            'country' => 'nullable|max:60',
        ]);
        User::update((int) $user['id'], [
            'name'    => $data['name'],
            'email'   => strtolower($data['email']),
            'phone'   => $data['phone'] ?? null,
            'country' => $data['country'] ?? null,
        ]);
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
        $this->redirect('/mi-cuenta', 'Listo, no se va a renovar. Seguís con acceso hasta el ' . fecha($membership['ends_at']) . '.');
    }
}
