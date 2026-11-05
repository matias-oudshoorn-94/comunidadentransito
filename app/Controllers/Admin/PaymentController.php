<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Payment;
use App\Services\MembershipService;

final class PaymentController extends AdminController
{
    public function index(): void
    {
        $filters = [
            'estado'  => (string) $this->request->query('estado', ''),
            'medio'   => (string) $this->request->query('medio', ''),
            'desde'   => (string) $this->request->query('desde', ''),
            'hasta'   => (string) $this->request->query('hasta', ''),
        ];
        $payments = Payment::list($filters['estado'], $filters['medio'], $filters['desde'], $filters['hasta']);

        $totals = [];
        foreach ($payments as $p) {
            if ($p['status'] === 'approved') {
                $totals[$p['currency']] = ($totals[$p['currency']] ?? 0) + (float) $p['amount'];
            }
        }

        $this->view('admin/payments', [
            'title'    => 'Pagos',
            'payments' => $payments,
            'filters'  => $filters,
            'totals'   => $totals,
        ]);
    }

    /** Aprobar a mano (transferencia confirmada), rechazar o marcar reintegro */
    public function update(string $id): void
    {
        $payment = Payment::findOrFail($id);
        match ($this->request->string('action')) {
            'approve' => MembershipService::approvePayment((int) $payment['id']),
            'reject'  => MembershipService::rejectPayment((int) $payment['id']),
            'refund'  => Payment::update((int) $payment['id'], ['status' => 'refunded']),
            default   => $this->abort(400),
        };
        $this->back('Pago actualizado.');
    }
}
