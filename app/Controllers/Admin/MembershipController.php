<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Membership;
use App\Services\MembershipService;

final class MembershipController extends AdminController
{
    public function index(): void
    {
        MembershipService::expireOverdue();
        $status = (string) $this->request->query('estado', 'active');
        $tier = (string) $this->request->query('nivel', '');
        $this->view('admin/memberships', [
            'title'       => 'Membresías',
            'memberships' => Membership::list($status === 'todas' ? '' : $status, $tier),
            'status'      => $status,
            'tier'        => $tier,
        ]);
    }

    public function update(string $id): void
    {
        $membership = Membership::findOrFail($id);
        $action = $this->request->string('action');
        match ($action) {
            'extend'       => MembershipService::extend((int) $membership['id'], max(1, $this->request->int('months', 1))),
            'stop_renewal' => MembershipService::stopRenewal((int) $membership['id']),
            'revoke'       => MembershipService::revoke((int) $membership['id']),
            default        => $this->abort(400),
        };
        $messages = ['extend' => 'Membresía extendida.', 'stop_renewal' => 'Renovación desactivada.', 'revoke' => 'Acceso cortado.'];
        $this->back($messages[$action]);
    }
}
