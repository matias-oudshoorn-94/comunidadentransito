<?php
/** C3 · Bienvenida  @var array|null $payment @var array|null $membership */
$approved = $payment && $payment['status'] === 'approved';
$pending = $payment && $payment['status'] === 'pending';
?>
<section class="section has-washes">
    <?= partial('partials/washes', ['animated' => true]) ?>
    <div class="container welcome">
        <?php if ($pending && !$membership): ?>
            <div class="welcome__icon"><?= icon('calendar') ?></div>
            <span class="kicker">Pago en proceso</span>
            <h1>Estamos esperando la confirmación</h1>
            <p class="lead">Apenas <?= $payment['gateway'] === 'paypal' ? 'PayPal' : 'Mercado Pago' ?> nos avise, se activa tu membresía y te llega un email. Suele tardar unos minutos.</p>
            <div class="actions" style="justify-content:center"><a class="btn" href="/bienvenida?pago=<?= (int) $payment['id'] ?>">Volver a revisar</a><a class="btn btn--ghost" href="/contenidos">Ver los adelantos</a></div>
        <?php else: ?>
            <div class="welcome__icon"><?= icon('heart') ?></div>
            <span class="kicker"><?= $approved ? 'Pago confirmado' : 'Bienvenida' ?></span>
            <h1>Te damos la bienvenida a la comunidad</h1>
            <?php if ($membership): ?>
                <p class="lead">Tu membresía <strong><?= e($membership['plan_name']) ?></strong> está activa hasta el <?= fecha($membership['ends_at']) ?>.</p>
            <?php endif; ?>
            <p class="hand" style="font-size:2rem">qué alegría que estés acá</p>
            <div class="actions" style="justify-content:center;margin-top:24px">
                <a class="btn" href="/mi-comunidad">Entrar a Mi Comunidad <?= icon('arrow', 'icon icon--move') ?></a>
                <a class="btn btn--ghost" href="/contenidos/runas-de-letras">Sacar mi primera runa</a>
            </div>
        <?php endif; ?>
    </div>
</section>
