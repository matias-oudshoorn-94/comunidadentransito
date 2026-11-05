<?php
/** D2 · Mi cuenta  @var array $user @var array|null $current @var array $payments */
$address = trim(implode(', ', array_filter([$user['address'] ?? '', $user['city'] ?? '', $user['postal_code'] ?? ''])));
$row = function (string $label, string $value, ?string $action, string $field = '', string $form = '') { ?>
    <div class="acc-row" <?= $form ? 'data-editable' : '' ?>>
        <div class="acc-row__line">
            <div><span class="acc-row__label"><?= e($label) ?></span><span class="acc-row__value"><?= $value ?></span></div>
            <?php if ($action && $form): ?><button class="btn--link" type="button" data-edit-toggle><?= e($action) ?></button>
            <?php elseif ($action): ?><?= $action ?><?php endif; ?>
        </div>
        <?= $form ?>
    </div>
<?php };
$field = fn (string $name, string $value, string $type = 'text', string $ph = '') => '<input class="input" name="' . $name . '" type="' . $type . '" value="' . e($value) . '" placeholder="' . e($ph) . '">';
$form = fn (string $action, string $inner) => '<form class="acc-row__form form" method="post" action="' . $action . '">' . csrf_field() . $inner . '<div class="actions"><button class="btn btn--sm" type="submit">Guardar</button><button class="btn--link" type="button" data-edit-toggle>Cancelar</button></div></form>';
?>
<section class="member-head">
    <div class="container">
        <span class="kicker">Mi cuenta</span>
        <h1>Mi cuenta</h1>
        <span class="hand">todo en orden, sin vueltas</span>
    </div>
</section>

<div class="container" style="padding-bottom:96px">
    <div class="account">
        <div class="account__col">
            <section class="acc-card">
                <h2>Tu membresía</h2>
                <?php if ($current): ?>
                    <?php $row('Plan', e($current['plan_name']), '<a class="btn--link" href="/membresias">Cambiar plan</a>'); ?>
                    <?php $row($current['auto_renew'] ? 'Próximo cobro' : 'Acceso hasta', e(fecha_dia_mes($current['ends_at'])) . ($current['auto_renew'] && $current['last_amount'] ? ' · ' . money($current['last_amount']) : ''), null); ?>
                    <?php $row('Medio de pago', e(medio($current['gateway'])), $current['gateway'] === 'mercadopago' ? '<a class="btn--link" href="https://www.mercadopago.com.ar/subscriptions" target="_blank" rel="noopener">Actualizar</a>' : null); ?>
                    <?php $row('Estado', '<span class="status-pill">Activa</span>', null); ?>
                <?php else: ?>
                    <?php $row('Estado', '<span class="status-pill status-pill--off">Sin membresía activa</span>', '<a class="btn--link" href="/membresias">Elegir plan</a>'); ?>
                <?php endif; ?>
            </section>

            <section class="acc-card">
                <h2>Historial de pagos</h2>
                <?php foreach (array_filter($payments, fn ($p) => $p['status'] !== 'pending') as $p): ?>
                    <?php $row(ucfirst(mes_nombre((int) date('n', strtotime($p['paid_at'] ?? $p['created_at'])))) . ' ' . date('Y', strtotime($p['paid_at'] ?? $p['created_at'])),
                        money($p['amount'], $p['currency']) . ' · ' . ($p['status'] === 'approved' ? 'Pagado' : e(estado($p['status']))),
                        $p['status'] === 'approved' ? '<a class="btn--link" href="/mi-cuenta/comprobante/' . (int) $p['id'] . '">Comprobante</a>' : null); ?>
                <?php endforeach; ?>
                <?php if (!array_filter($payments, fn ($p) => $p['status'] !== 'pending')): ?><p class="muted" style="padding:12px 0">Todavía no hay pagos.</p><?php endif; ?>
            </section>
        </div>

        <section class="acc-card">
            <h2>Tus datos</h2>
            <?php $row('Nombre', e($user['name']), 'Editar', 'name', $form('/mi-cuenta/datos', $field('name', $user['name'], 'text', 'Nombre y apellido'))); ?>
            <?php $row('Email', e($user['email']), 'Editar', 'email', $form('/mi-cuenta/datos', $field('email', $user['email'], 'email'))); ?>
            <?php $row('Dirección para la carta', $address !== '' ? e($address) : '<span class="muted">Sin cargar</span>', 'Editar', 'address',
                $form('/mi-cuenta/datos', $field('address', $user['address'] ?? '', 'text', 'Calle y número') . '<div class="form-row">' . $field('city', $user['city'] ?? '', 'text', 'Ciudad') . $field('postal_code', $user['postal_code'] ?? '', 'text', 'Código postal') . '</div>')); ?>
            <?php $row('Contraseña', '••••••••', 'Cambiar', 'password',
                $form('/mi-cuenta/clave', $field('current_password', '', 'password', 'Contraseña actual') . $field('password', '', 'password', 'Nueva contraseña (mínimo 8)') . $field('password_confirmation', '', 'password', 'Repetila'))); ?>
            <?php foreach (['name', 'email', 'address', 'city', 'current_password', 'password', 'password_confirmation'] as $f): if ($m = error($f)): ?>
                <p class="field__error"><?= e($m) ?></p>
            <?php endif; endforeach; ?>
        </section>
    </div>

    <div class="cancel-box">
        <h3>¿Querés pausar o cancelar tu membresía?</h3>
        <p>Podés hacerlo cuando quieras. Seguís teniendo acceso hasta el final del período pagado.</p>
        <?php if ($current && $current['auto_renew']): ?>
            <form method="post" action="/mi-cuenta/renovacion" data-confirm="¿Cancelar la renovación? Seguís con acceso hasta el <?= e(fecha($current['ends_at'])) ?>.">
                <?= csrf_field() ?><input type="hidden" name="membership_id" value="<?= (int) $current['id'] ?>">
                <button class="btn--link" type="submit">Cancelar membresía</button>
            </form>
        <?php elseif ($current): ?>
            <p class="small muted">Tu membresía no se renueva: termina el <?= fecha($current['ends_at']) ?>.</p>
        <?php endif; ?>
    </div>
</div>
