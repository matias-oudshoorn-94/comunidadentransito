<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Membership;
use App\Models\Payment;

/** Calendario mensual: vencimientos, altas y cobros. */
final class CalendarController extends AdminController
{
    public function index(): void
    {
        $month = (string) $this->request->query('mes', date('Y-m'));
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month = date('Y-m');
        }
        $first = strtotime($month . '-01');
        $from = date('Y-m-01 00:00:00', $first);
        $to = date('Y-m-t 23:59:59', $first);

        $events = [];
        foreach (Membership::endingBetween($from, $to) as $m) {
            $events[substr($m['ends_at'], 0, 10)][] = [
                'type'  => 'vence',
                'label' => $m['user_name'],
                'meta'  => $m['plan_name'] . ($m['auto_renew'] ? ' · se renueva' : ''),
                'url'   => '/admin/usuarios/' . $m['user_id'],
            ];
        }
        foreach (Membership::startingBetween($from, $to) as $m) {
            $events[substr($m['starts_at'], 0, 10)][] = [
                'type'  => 'alta',
                'label' => $m['user_name'],
                'meta'  => $m['plan_name'],
                'url'   => '/admin/usuarios/' . $m['user_id'],
            ];
        }
        foreach (Payment::approvedBetween($from, $to) as $p) {
            $events[substr($p['paid_at'], 0, 10)][] = [
                'type'  => 'pago',
                'label' => $p['user_name'],
                'meta'  => money($p['amount'], $p['currency']),
                'url'   => '/admin/usuarios/' . $p['user_id'],
            ];
        }

        // Grilla de semanas que empieza en lunes
        $offset = ((int) date('N', $first)) - 1;
        $days = (int) date('t', $first);
        $cells = array_fill(0, $offset, null);
        for ($d = 1; $d <= $days; $d++) {
            $cells[] = sprintf('%s-%02d', $month, $d);
        }
        while (count($cells) % 7 !== 0) {
            $cells[] = null;
        }

        $this->view('admin/calendar', [
            'title'  => 'Calendario',
            'month'  => $month,
            'label'  => ucfirst(mes_nombre((int) date('n', $first))) . ' ' . date('Y', $first),
            'prev'   => date('Y-m', strtotime('-1 month', $first)),
            'next'   => date('Y-m', strtotime('+1 month', $first)),
            'weeks'  => array_chunk($cells, 7),
            'events' => $events,
        ]);
    }
}
