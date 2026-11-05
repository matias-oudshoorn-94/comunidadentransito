<?php
/** C1 · Compra  @var array $plan @var array $plans @var array $gateways */
$user = auth();
$tiers = config('site.tiers');
$periodText = ['mensual' => 'por mes', 'semestral' => 'cada 6 meses', 'anual' => 'por año'];
$selectedGateway = old('gateway', array_key_first($gateways) ?? '');
?>
<section class="page-hero" style="padding-bottom:24px">
    <?= partial('partials/washes') ?>
    <div class="container">
        <nav class="breadcrumb" aria-label="Ruta"><a href="/membresias">Membresías</a> <span aria-hidden="true">/</span> <span>Compra</span></nav>
        <span class="kicker">Compra</span>
        <h1>Ya casi estás adentro</h1>
    </div>
</section>

<section class="section section--tight" style="padding-top:0">
    <div class="container checkout">
        <form class="form" method="post" action="/compra" novalidate>
            <?= csrf_field() ?>
            <input type="hidden" name="plan_id" value="<?= (int) $plan['id'] ?>">

            <?php if (!$user): ?>
                <div class="panel form">
                    <div>
                        <h2 style="font-size:1.4rem;margin-bottom:4px">1. Tu cuenta</h2>
                        <p class="small muted" style="margin:0">¿Ya tenés cuenta? <a href="/ingresar">Ingresá</a> y volvé a esta página.</p>
                    </div>
                    <div class="field <?= error('name') ? 'has-error' : '' ?>">
                        <label for="name">Nombre</label>
                        <input class="input" id="name" name="name" value="<?= e(old('name')) ?>" autocomplete="name" required>
                        <?= partial('partials/field-error', ['name' => 'name']) ?>
                    </div>
                    <div class="form-row">
                        <div class="field <?= error('email') ? 'has-error' : '' ?>">
                            <label for="email">Email</label>
                            <input class="input" id="email" name="email" type="email" value="<?= e(old('email')) ?>" autocomplete="email" required>
                            <?= partial('partials/field-error', ['name' => 'email']) ?>
                        </div>
                        <div class="field <?= error('password') ? 'has-error' : '' ?>">
                            <label for="password">Contraseña</label>
                            <input class="input" id="password" name="password" type="password" autocomplete="new-password" minlength="8" required>
                            <?= partial('partials/field-error', ['name' => 'password']) ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="notice"><?= icon('user') ?><span>Comprás como <strong><?= e($user['name']) ?></strong> (<?= e($user['email']) ?>).</span></div>
            <?php endif; ?>

            <div class="panel form">
                <h2 style="font-size:1.4rem;margin:0"><?= $user ? '1' : '2' ?>. Medio de pago</h2>
                <?php if ($gateways === []): ?>
                    <div class="notice notice--alert"><?= icon('card') ?><span>Los pagos online no están disponibles en este momento. Escribinos por <a href="/contacto">contacto</a>.</span></div>
                <?php endif; ?>
                <?php foreach ($gateways as $key => $g): ?>
                    <label class="gateway-option">
                        <input type="radio" name="gateway" value="<?= e($key) ?>" <?= $selectedGateway === $key ? 'checked' : '' ?>>
                        <span class="gateway-option__box">
                            <span class="gateway-option__dot"></span>
                            <span><strong><?= e($g['label']) ?></strong><br><span class="small muted"><?= e($g['hint']) ?></span></span>
                            <span class="gateway-option__price"><?= money($g['currency'] === 'USD' ? $plan['price_usd'] : $plan['price_ars'], $g['currency']) ?></span>
                        </span>
                    </label>
                <?php endforeach; ?>

                <label class="check <?= error('terms') ? 'has-error' : '' ?>">
                    <input type="checkbox" name="terms" value="1" required>
                    <span>Acepto las condiciones de la membresía. Puedo darme de baja cuando quiera desde Mi cuenta.</span>
                </label>
                <?= partial('partials/field-error', ['name' => 'terms']) ?>

                <button class="btn btn--block" type="submit" <?= $gateways === [] ? 'disabled' : '' ?>>Ir a pagar <?= icon('arrow', 'icon icon--move') ?></button>
                <p class="small muted center" style="margin:0"><?= icon('lock', 'icon') ?> El pago se hace en el sitio seguro de Mercado Pago o PayPal.</p>
            </div>
        </form>

        <aside class="checkout__summary panel panel--sky" aria-label="Resumen">
            <span class="kicker">Tu membresía</span>
            <h2 style="font-size:1.7rem"><?= e($plan['name']) ?></h2>
            <p class="muted"><?= e($plan['description']) ?></p>
            <div class="summary-row"><span>Nivel</span><strong><?= e($tiers[$plan['tier']]['name']) ?></strong></div>
            <div class="summary-row"><span>Duración</span><strong><?= (int) $plan['months'] ?> <?= (int) $plan['months'] === 1 ? 'mes' : 'meses' ?></strong></div>
            <div class="summary-row"><span>Total</span><span class="summary-total"><?= money($plan['price_ars']) ?></span></div>
            <p class="small muted" style="margin:6px 0 16px"><?= e($periodText[$plan['period']]) ?> · o <?= money($plan['price_usd'], 'USD') ?> con PayPal</p>
            <details>
                <summary class="small" style="cursor:pointer;color:var(--turquesa);font-weight:700">Cambiar de plan</summary>
                <ul class="list-plain" style="margin-top:8px">
                    <?php foreach ($plans as $p): if ((int) $p['id'] === (int) $plan['id']) continue; ?>
                        <li><a class="list-row" href="/compra?plan=<?= e($p['slug']) ?>"><span><?= e($p['name']) ?></span><span class="small"><?= money($p['price_ars']) ?></span></a></li>
                    <?php endforeach; ?>
                </ul>
            </details>
        </aside>
    </div>
</section>
