<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\ContactMessage;
use App\Models\Membership;
use App\Models\Payment;
use App\Models\User;
use App\Services\MembershipService;

final class DashboardController extends AdminController
{
    public function index(): void
    {
        MembershipService::expireOverdue();

        $monthStart = date('Y-m-01 00:00:00');
        $monthEnd = date('Y-m-t 23:59:59');

        // Cobros de los últimos 6 meses, para el gráfico
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $ts = strtotime(date('Y-m-01') . " -{$i} months");
            $totals = Payment::totals(date('Y-m-01 00:00:00', $ts), date('Y-m-t 23:59:59', $ts));
            $months[] = ['label' => ucfirst(substr(mes_nombre((int) date('n', $ts)), 0, 3)), 'ars' => (float) ($totals['ARS'] ?? 0), 'usd' => (float) ($totals['USD'] ?? 0)];
        }

        $this->view('admin/dashboard', [
            'title'     => 'Resumen',
            'active'    => Membership::activeCount(),
            'orilla'    => Membership::activeCount('orilla'),
            'inmersion' => Membership::activeCount('inmersion'),
            'revenue'   => Payment::totals($monthStart, $monthEnd),
            'newUsers'  => User::count("role = 'member' AND created_at >= ?", [$monthStart]),
            'pending'   => Payment::count("status = 'pending'"),
            'unread'    => ContactMessage::unreadCount(),
            'expiring'  => Membership::expiringSoon(30, 8),
            'expiringCount' => count(Membership::list('por-vencer')),
            'payments'  => Payment::recent(8),
            'months'    => $months,
        ]);
    }
}
