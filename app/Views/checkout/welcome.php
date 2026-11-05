<?php
/** C3 · Bienvenida  @var array|null $payment @var array|null $membership @var array|null $event */
$user = auth();
$pending = $payment && $payment['status'] === 'pending' && !$membership;
$group = (string) config('site.social.whatsapp_group');
?>
<div class="container welcome">
    <div>
        <?php if ($pending): ?>
            <span class="kicker">Pago en proceso</span>
            <h1>Estamos esperando la confirmación</h1>
            <p class="lead">Apenas <?= $payment['gateway'] === 'paypal' ? 'PayPal' : 'Mercado Pago' ?> nos avise, se activa tu membresía y te llega un email. Suele tardar unos minutos.</p>
            <div class="actions" style="margin-top:24px"><a class="btn" href="/bienvenida?pago=<?= (int) $payment['id'] ?>">Volver a revisar</a><a class="link-arrow" href="/contenidos">Ver los adelantos →</a></div>
        <?php else: ?>
            <span class="kicker">¡Ya sos parte!</span>
            <h1>Te damos la bienvenida, <?= e(first_name($user['name'])) ?></h1>
            <span class="hand">qué alegría que estés acá</span>
            <p class="lead">Esto es lo que pasa ahora. Andá a tu ritmo: no hay apuro.</p>
            <div class="welcome__list">
                <div class="row-card">
                    <span class="icon-dot"><?= icon('cup') ?></span>
                    <div class="row-card__text"><h3>Sumate a Cafetería en tránsito</h3><p>Entrá al grupo de WhatsApp de la Comunidad.</p></div>
                    <a class="btn btn--sm" href="<?= e($group ?: '/contenidos/cafeteria-en-transito') ?>" <?= $group ? 'target="_blank" rel="noopener"' : '' ?>>Unirme al grupo</a>
                </div>
                <div class="row-card">
                    <span class="icon-dot"><?= icon('mail') ?></span>
                    <div class="row-card__text"><h3>Tu primera carta</h3><p>Llega a tu casa por correo en los próximos días.</p></div>
                </div>
                <div class="row-card">
                    <span class="icon-dot"><?= icon('users') ?></span>
                    <div class="row-card__text"><h3>Próximo encuentro</h3><p><?= $event ? e(fecha($event['event_at'], true)) . ' · por Zoom' : 'Fecha y hora a confirmar con Silvina.' ?></p></div>
                </div>
            </div>
            <a class="btn btn--lg" href="/mi-comunidad">Ir a Mi Comunidad</a>
        <?php endif; ?>
    </div>
    <div class="hero__photo photo-halo"><div class="photo" style="aspect-ratio:420/460"><?= img('fotos/cafe-taza.jpg', 'Un café sobre la mesa', '') ?></div></div>
</div>
