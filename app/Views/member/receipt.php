<?php /** Comprobante de pago  @var array $payment @var array $user */ ?>
<div class="container receipt">
    <div class="panel">
        <img src="<?= asset('img/logo-sintetico.webp') ?>" alt="Comunidad en tránsito" style="width:140px;margin-bottom:24px">
        <span class="kicker">Comprobante de pago</span>
        <h1 style="font-size:2rem">Pago #<?= (int) $payment['id'] ?></h1>
        <div class="summary__row"><span>Fecha</span><strong><?= fecha($payment['paid_at'] ?? $payment['created_at']) ?></strong></div>
        <div class="summary__row"><span>Nombre</span><strong><?= e($user['name']) ?></strong></div>
        <div class="summary__row"><span>Email</span><strong><?= e($user['email']) ?></strong></div>
        <div class="summary__row"><span>Concepto</span><strong>Membresía <?= e($payment['plan_name']) ?></strong></div>
        <div class="summary__row"><span>Medio de pago</span><strong><?= e(medio($payment['gateway'])) ?></strong></div>
        <?php if ($payment['gateway_payment_id']): ?><div class="summary__row"><span>Referencia</span><strong><?= e($payment['gateway_payment_id']) ?></strong></div><?php endif; ?>
        <div class="summary__total"><span>Total</span><strong><?= money($payment['amount'], $payment['currency']) ?></strong></div>
        <p class="small muted">Este comprobante no es una factura.</p>
        <div class="actions no-print"><button class="btn" type="button" onclick="window.print()">Imprimir o guardar PDF</button><a class="link-arrow" href="/mi-cuenta">Volver a Mi cuenta</a></div>
    </div>
</div>
