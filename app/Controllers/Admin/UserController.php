<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Membership;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\User;
use App\Services\MembershipService;

final class UserController extends AdminController
{
    public function index(): void
    {
        $q = trim((string) $this->request->query('q', ''));
        $status = (string) $this->request->query('estado', '');
        $page = max(1, (int) $this->request->query('pagina', 1));

        $this->view('admin/users/index', [
            'title'  => 'Usuarios',
            'result' => User::search($q, $status, $page),
            'q'      => $q,
            'status' => $status,
        ]);
    }

    public function show(string $id): void
    {
        $user = User::findOrFail($id);
        $this->view('admin/users/show', [
            'title'       => $user['name'],
            'user'        => $user,
            'current'     => Membership::currentFor((int) $user['id']),
            'memberships' => Membership::forUser((int) $user['id']),
            'payments'    => Payment::forUser((int) $user['id']),
            'plans'       => Plan::all('sort ASC'),
        ]);
    }

    public function update(string $id): void
    {
        $user = User::findOrFail($id);
        $data = $this->validate([
            'name'     => 'required|max:120',
            'email'    => 'required|email|max:190|unique:users,email,' . $user['id'],
            'phone'    => 'nullable|max:40',
            'country'  => 'nullable|max:60',
            'notes'    => 'nullable|max:5000',
            'password' => 'nullable|min:8|max:200',
        ]);
        $update = [
            'name'    => $data['name'],
            'email'   => strtolower($data['email']),
            'phone'   => $data['phone'] ?? null,
            'country' => $data['country'] ?? null,
            'notes'   => $data['notes'] ?? null,
        ];
        if (!empty($data['password'])) {
            $update['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        User::update((int) $user['id'], $update);
        $this->redirect('/admin/usuarios/' . $user['id'], 'Datos guardados.');
    }

    /** Asignar una membresía a mano (pago por transferencia, regalo, etc.) */
    public function assignMembership(string $id): void
    {
        $user = User::findOrFail($id);
        $data = $this->validate([
            'plan_id'   => 'required|numeric',
            'starts_at' => 'required|date',
        ]);
        MembershipService::assignManual(
            (int) $user['id'],
            (int) $data['plan_id'],
            $data['starts_at'],
            $this->request->bool('record_payment'),
            'manual',
            $this->request->string('note') ?: 'Asignada desde el admin',
        );
        $this->redirect('/admin/usuarios/' . $user['id'], 'Membresía asignada.');
    }
}
