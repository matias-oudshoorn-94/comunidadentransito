<?php /** Solo en desarrollo  @var array $payment @var array $plan @var string $label */ ?>
<section class="auth">
    <?= partial('partials/washes') ?>
    <div class="auth__card panel">
        <span class="chip chip--sand" style="margin-bottom:16px">Modo desarrollo</span>
        <h1 style="font-size:2rem">Pago simulado con <?= e($label) ?></h1>
        <p class="muted">No hay credenciales de <?= e($label) ?> en el <code>.env</code>, así que simulamos la respuesta de la pasarela para probar el circuito completo.</p>
        <div class="summary-row"><span><?= e($plan['name']) ?></span><strong><?= money($payment['amount'], $payment['currency']) ?></strong></div>
        <form method="post" class="actions" style="margin-top:24px">
            <?= csrf_field() ?>
            <button class="btn" name="result" value="approved" type="submit"><?= icon('check') ?> Aprobar pago</button>
            <button class="btn btn--ghost" name="result" value="rejected" type="submit">Rechazar</button>
        </form>
    </div>
</section>
