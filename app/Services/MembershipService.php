<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\Membership;
use App\Models\Payment;
use App\Models\Plan;
use DateTimeImmutable;

/**
 * Ciclo de vida de las membresías:
 *   compra → membresía y pago "pending" → la pasarela confirma → pago "approved" y membresía "active"
 *   renovación automática (Mercado Pago) → nuevo pago aprobado → se extiende ends_at
 *   baja → auto_renew = 0, mantiene acceso hasta ends_at → bin/expire.php la marca "expired"
 */
final class MembershipService
{
    /** Crea la membresía y el pago pendientes para una compra. */
    public static function startCheckout(array $user, array $plan, string $gateway, string $currency): array
    {
        return Database::transaction(function () use ($user, $plan, $gateway, $currency) {
            $membershipId = Membership::create([
                'user_id'    => (int) $user['id'],
                'plan_id'    => (int) $plan['id'],
                'status'     => 'pending',
                'auto_renew' => $gateway === 'mercadopago' ? 1 : 0,
                'gateway'    => $gateway,
            ]);
            $paymentId = Payment::create([
                'user_id'       => (int) $user['id'],
                'membership_id' => $membershipId,
                'plan_id'       => (int) $plan['id'],
                'gateway'       => $gateway,
                'amount'        => Plan::price($plan, $currency),
                'currency'      => $currency,
                'status'        => 'pending',
            ]);
            return ['membership_id' => $membershipId, 'payment_id' => $paymentId];
        });
    }

    /**
     * La pasarela confirmó el pago: se activa la membresía.
     * Es idempotente: si el retorno y el webhook llegan los dos, no se duplica nada.
     */
    public static function approvePayment(int $paymentId, ?string $gatewayPaymentId = null, array $raw = []): void
    {
        Database::transaction(function () use ($paymentId, $gatewayPaymentId, $raw) {
            $payment = Payment::find($paymentId);
            if (!$payment || $payment['status'] === 'approved') {
                return;
            }
            $data = ['status' => 'approved', 'paid_at' => now(), 'raw' => $raw ? json_encode($raw) : $payment['raw']];
            if ($gatewayPaymentId !== null && !Payment::findByGatewayPaymentId($gatewayPaymentId)) {
                $data['gateway_payment_id'] = $gatewayPaymentId;
            }
            Payment::update($paymentId, $data);

            if ($payment['membership_id']) {
                self::activate((int) $payment['membership_id']);
            }
        });
    }

    public static function rejectPayment(int $paymentId, array $raw = []): void
    {
        $payment = Payment::find($paymentId);
        if (!$payment || $payment['status'] !== 'pending') {
            return;
        }
        Payment::update($paymentId, ['status' => 'rejected', 'raw' => $raw ? json_encode($raw) : null]);
        if ($payment['membership_id']) {
            $membership = Membership::find((int) $payment['membership_id']);
            if ($membership && $membership['status'] === 'pending') {
                Membership::update((int) $membership['id'], ['status' => 'cancelled', 'cancelled_at' => now()]);
            }
        }
    }

    /**
     * Activa una membresía. Si la persona ya tiene una vigente del mismo nivel,
     * la nueva empieza cuando termina la anterior (así no pierde días).
     */
    public static function activate(int $membershipId, ?string $startsAt = null): void
    {
        $membership = Membership::withDetails($membershipId);
        if (!$membership || $membership['status'] === 'active') {
            return;
        }
        $start = $startsAt ?? now();
        if ($startsAt === null) {
            $lastEnd = Database::value(
                "SELECT MAX(m.ends_at) FROM memberships m JOIN plans p ON p.id = m.plan_id
                 WHERE m.user_id = ? AND p.tier = ? AND m.status = 'active' AND m.ends_at > ? AND m.id <> ?",
                [$membership['user_id'], $membership['tier'], now(), $membershipId],
            );
            if ($lastEnd) {
                $start = (string) $lastEnd;
            }
        }
        Membership::update($membershipId, [
            'status'    => 'active',
            'starts_at' => $start,
            'ends_at'   => self::addMonths($start, (int) $membership['months']),
        ]);
        AccessService::forget((int) $membership['user_id']);
    }

    /**
     * Cobro recurrente de la suscripción: extiende el período.
     * El primer cobro de una suscripción se asocia al pago inicial en lugar de extender.
     */
    public static function recordRenewal(int $membershipId, string $gatewayPaymentId, float $amount, string $currency, array $raw = []): void
    {
        Database::transaction(function () use ($membershipId, $gatewayPaymentId, $amount, $currency, $raw) {
            if (Payment::findByGatewayPaymentId($gatewayPaymentId)) {
                return; // ya registrado
            }
            $membership = Membership::withDetails($membershipId);
            if (!$membership) {
                return;
            }

            $initial = Database::one(
                "SELECT * FROM payments WHERE membership_id = ? AND gateway_payment_id IS NULL AND status IN ('approved', 'pending') ORDER BY id ASC LIMIT 1",
                [$membershipId],
            );
            if ($initial) {
                Payment::update((int) $initial['id'], ['gateway_payment_id' => $gatewayPaymentId]);
                self::approvePayment((int) $initial['id'], null, $raw);
                return;
            }

            Payment::create([
                'user_id'            => (int) $membership['user_id'],
                'membership_id'      => $membershipId,
                'plan_id'            => (int) $membership['plan_id'],
                'gateway'            => (string) $membership['gateway'],
                'gateway_reference'  => $membership['gateway_subscription_id'],
                'gateway_payment_id' => $gatewayPaymentId,
                'amount'             => $amount,
                'currency'           => $currency,
                'status'             => 'approved',
                'paid_at'            => now(),
                'note'               => 'Renovación automática',
                'raw'                => json_encode($raw),
            ]);

            $base = max((string) $membership['ends_at'], now());
            Membership::update($membershipId, [
                'status'  => 'active',
                'ends_at' => self::addMonths($base, (int) $membership['months']),
            ]);
        });
    }

    /** Alta manual desde el admin (o datos de prueba). */
    public static function assignManual(int $userId, int $planId, string $startsAt, bool $recordPayment, string $gateway = 'manual', ?string $note = null): int
    {
        $plan = Plan::findOrFail($planId);
        return Database::transaction(function () use ($userId, $plan, $startsAt, $recordPayment, $gateway, $note) {
            $start = date('Y-m-d H:i:s', strtotime($startsAt));
            $end = self::addMonths($start, (int) $plan['months']);
            $membershipId = Membership::create([
                'user_id'    => $userId,
                'plan_id'    => (int) $plan['id'],
                'status'     => $end < now() ? 'expired' : 'active',
                'starts_at'  => $start,
                'ends_at'    => $end,
                'auto_renew' => $gateway === 'mercadopago' ? 1 : 0,
                'gateway'    => $gateway,
            ]);
            if ($recordPayment) {
                $currency = $gateway === 'paypal' ? 'USD' : 'ARS';
                Payment::create([
                    'user_id'       => $userId,
                    'membership_id' => $membershipId,
                    'plan_id'       => (int) $plan['id'],
                    'gateway'       => $gateway,
                    'amount'        => Plan::price($plan, $currency),
                    'currency'      => $currency,
                    'status'        => 'approved',
                    'paid_at'       => $start,
                    'note'          => $note,
                    'created_at'    => $start,
                ]);
            }
            AccessService::forget($userId);
            return $membershipId;
        });
    }

    /** La persona da de baja la renovación: mantiene el acceso hasta el final del período. */
    public static function stopRenewal(int $membershipId): void
    {
        Membership::update($membershipId, ['auto_renew' => 0, 'cancelled_at' => now()]);
    }

    /** Admin: corta el acceso ya mismo. */
    public static function revoke(int $membershipId): void
    {
        Membership::update($membershipId, ['status' => 'cancelled', 'auto_renew' => 0, 'cancelled_at' => now()]);
    }

    public static function extend(int $membershipId, int $months): void
    {
        $membership = Membership::findOrFail($membershipId);
        $base = max((string) $membership['ends_at'], now());
        Membership::update($membershipId, ['status' => 'active', 'ends_at' => self::addMonths($base, $months)]);
    }

    public static function expireOverdue(): int
    {
        return Database::update('memberships', ['status' => 'expired', 'updated_at' => now()], "status = 'active' AND ends_at < ?", [now()]);
    }

    /** Suma meses sin desbordar (31 de enero + 1 mes = 28/29 de febrero). */
    public static function addMonths(string $date, int $months): string
    {
        $d = new DateTimeImmutable($date);
        $target = $d->modify('first day of this month')->modify("+{$months} months");
        $day = min((int) $d->format('j'), (int) $target->format('t'));
        return $target->setDate((int) $target->format('Y'), (int) $target->format('n'), $day)->format('Y-m-d H:i:s');
    }
}
