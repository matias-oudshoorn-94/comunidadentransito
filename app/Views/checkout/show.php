<?php
/** C1 · Compra  @var array $plan @var array $gateways */
$user = auth();
$tiers = config('site.tiers');
$periodText = ['mensual' => 'Mensual', 'semestral' => 'Semestral', 'anual' => 'Anual'];
$renewText = ['mensual' => 'Cada mes', 'semestral' => 'Cada 6 meses', 'anual' => 'Cada año'];
$hasMp = isset($gateways['mercadopago']);
?>
<div class="container" style="padding-bottom:96px">
    <ol class="stepper" aria-label="Pasos de la compra">
        <li class="stepper__step is-done"><span class="stepper__dot"><?= icon('check') ?></span> Plan</li>
        <li class="stepper__line" aria-hidden="true"></li>
        <li class="stepper__step is-current" aria-current="step"><span class="stepper__dot">2</span> Tus datos</li>
        <li class="stepper__line" aria-hidden="true"></li>
        <li class="stepper__step"><span class="stepper__dot">3</span> Pago</li>
    </ol>

    <form class="checkout" method="post" action="/compra" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="plan_id" value="<?= (int) $plan['id'] ?>">

        <div class="panel form">
            <div>
                <h2>Tus datos</h2>
                <span class="hand" style="font-size:1.35rem;margin:0">así te damos la bienvenida</span>
            </div>
            <?php if (!$user): ?>
                <div class="field <?= error('name') ? 'has-error' : '' ?>">
                    <label for="name">Nombre y apellido</label>
                    <input class="input" id="name" name="name" value="<?= e(old('name')) ?>" autocomplete="name" placeholder="¿Cómo te llamás?" required>
                    <?= partial('partials/field-error', ['name' => 'name']) ?>
                </div>
                <div class="field <?= error('email') ? 'has-error' : '' ?>">
                    <label for="email">Email</label>
                    <input class="input" id="email" name="email" type="email" value="<?= e(old('email')) ?>" autocomplete="email" placeholder="nombre@correo.com" required>
                    <?= partial('partials/field-error', ['name' => 'email']) ?>
                </div>
                <div class="field <?= error('password') ? 'has-error' : '' ?>">
                    <label for="password">Creá una contraseña</label>
                    <input class="input" id="password" name="password" type="password" autocomplete="new-password" minlength="8" placeholder="Mínimo 8 caracteres" required>
                    <?= partial('partials/field-error', ['name' => 'password']) ?>
                </div>
                <p class="small muted" style="margin:-6px 0 0">¿Ya tenés cuenta? <a href="/ingresar">Ingresá</a> y volvé a esta página.</p>
            <?php else: ?>
                <div class="notice"><?= icon('user') ?><span>Te sumás como <strong><?= e($user['name']) ?></strong> (<?= e($user['email']) ?>).</span></div>
            <?php endif; ?>

            <div class="address-head">
                <span class="icon-dot"><?= icon('mail') ?></span>
                <div><h3>Dirección para la carta Slow Mood</h3><p>Adonde te llega la carta de Silvina cada mes.</p></div>
            </div>
            <div class="field <?= error('address') ? 'has-error' : '' ?>">
                <label for="address">Calle y número</label>
                <input class="input" id="address" name="address" value="<?= e(old('address', $user['address'] ?? '')) ?>" autocomplete="street-address" placeholder="Ej.: Av. Colón 1234, depto 3" required>
                <?= partial('partials/field-error', ['name' => 'address']) ?>
            </div>
            <div class="form-row">
                <div class="field <?= error('city') ? 'has-error' : '' ?>">
                    <label for="city">Ciudad</label>
                    <input class="input" id="city" name="city" value="<?= e(old('city', $user['city'] ?? '')) ?>" autocomplete="address-level2" placeholder="Mar del Plata" required>
                    <?= partial('partials/field-error', ['name' => 'city']) ?>
                </div>
                <div class="field <?= error('postal_code') ? 'has-error' : '' ?>">
                    <label for="postal_code">Código postal</label>
                    <input class="input" id="postal_code" name="postal_code" value="<?= e(old('postal_code', $user['postal_code'] ?? '')) ?>" autocomplete="postal-code" placeholder="7600">
                    <?= partial('partials/field-error', ['name' => 'postal_code']) ?>
                </div>
            </div>
        </div>

        <aside class="panel panel--sky summary" aria-label="Tu membresía">
            <h2>Tu membresía</h2>
            <div class="summary__plan">
                <?= img('fotos/cafe-taza.jpg', '', '') ?>
                <div><strong><?= e($tiers[$plan['tier']]['name']) ?></strong><span><?= e($periodText[$plan['period']]) ?> · <a href="/membresias">Cambiar plan</a></span></div>
            </div>
            <div class="summary__row"><span>Renovación</span><strong><?= e($renewText[$plan['period']]) ?></strong></div>
            <div class="summary__row"><span>Primera carta</span><strong>Llega en los próximos días</strong></div>
            <div class="summary__total"><span>Total hoy</span><strong><?= money($plan['price_ars']) ?></strong></div>
            <?php if ($hasMp): ?>
                <button class="btn btn--block btn--lg" type="submit" name="gateway" value="mercadopago">Pagar con Mercado Pago</button>
            <?php elseif ($gateways === []): ?>
                <div class="notice notice--alert">Los pagos online no están disponibles en este momento. Escribinos por WhatsApp.</div>
            <?php endif; ?>
            <p class="note">Cancelás cuando quieras desde Mi cuenta.</p>
            <?php if (isset($gateways['paypal'])): ?>
                <p class="summary__alt">¿Estás fuera de Argentina? <button class="btn--link" type="submit" name="gateway" value="paypal">Pagá con PayPal (<?= money($plan['price_usd'], 'USD') ?>)</button></p>
            <?php endif; ?>
            <a class="link-arrow small" href="<?= e(whatsapp('Hola Silvina, tengo una duda sobre la membresía.')) ?>" target="_blank" rel="noopener">¿Dudas? Escribime por WhatsApp</a>
        </aside>
    </form>
</div>
