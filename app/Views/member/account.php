<?php
/** D2 · Mi cuenta
 * @var array $user @var array|null $current @var array $memberships @var array $payments */
use App\Services\Payments\GatewayFactory;

$statusText = ['active' => 'Activa', 'expired' => 'Vencida', 'cancelled' => 'Cancelada', 'pending' => 'Pendiente', 'approved' => 'Aprobado', 'rejected' => 'Rechazado', 'refunded' => 'Reintegrado'];
?>
<section class="member-hero has-washes">
    <?= partial('partials/washes') ?>
    <div class="container">
        <span class="kicker">Mi cuenta</span>
        <h1>Tus datos y tu membresía</h1>
    </div>
</section>

<section class="section section--tight" style="padding-top:0">
    <div class="container member-grid">
        <div class="grid" style="gap:28px">
            <div class="panel">
                <h2 style="font-size:1.4rem">Membresía</h2>
                <?php if ($current): ?>
                    <div class="list-row">
                        <div><h4><?= e($current['plan_name']) ?></h4><span class="status status--active">Activa hasta el <?= fecha($current['ends_at']) ?></span></div>
                        <a class="btn btn--ghost btn--sm" href="/membresias">Cambiar plan</a>
                    </div>
                    <?php if ((int) $current['auto_renew'] === 1): ?>
                        <div class="notice" style="margin-top:16px">
                            <?= icon('calendar') ?>
                            <div>
                                <p style="margin:0 0 10px">Se renueva automáticamente con <?= e(GatewayFactory::LABELS[$current['gateway']] ?? $current['gateway']) ?> el <?= fecha($current['ends_at']) ?>.</p>
                                <form method="post" action="/mi-cuenta/renovacion" data-confirm="¿Querés dar de baja la renovación? Seguís con acceso hasta el <?= e(fecha($current['ends_at'])) ?>.">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="membership_id" value="<?= (int) $current['id'] ?>">
                                    <button class="btn--link" type="submit">Dar de baja la renovación</button>
                                </form>
                            </div>
                        </div>
                    <?php elseif ($current['cancelled_at']): ?>
                        <p class="small muted" style="margin-top:12px">Diste de baja la renovación. Tu acceso sigue hasta el <?= fecha($current['ends_at']) ?>.</p>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="muted">No tenés una membresía activa.</p>
                    <a class="btn" href="/membresias">Elegir membresía</a>
                <?php endif; ?>
            </div>

            <div class="panel">
                <h2 style="font-size:1.4rem">Historial de pagos</h2>
                <?php if ($payments): ?>
                    <div class="table-scroll">
                        <table class="table">
                            <thead><tr><th>Fecha</th><th>Plan</th><th>Medio</th><th>Monto</th><th>Estado</th></tr></thead>
                            <tbody>
                                <?php foreach ($payments as $p): ?>
                                    <tr>
                                        <td><?= fecha_corta($p['paid_at'] ?? $p['created_at']) ?></td>
                                        <td><?= e($p['plan_name']) ?></td>
                                        <td><?= e(GatewayFactory::LABELS[$p['gateway']] ?? $p['gateway']) ?></td>
                                        <td><?= money($p['amount'], $p['currency']) ?></td>
                                        <td><span class="status status--<?= e($p['status']) ?>"><?= e($statusText[$p['status']] ?? $p['status']) ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="muted" style="margin:0">Todavía no hay pagos.</p>
                <?php endif; ?>
            </div>
        </div>

        <aside class="grid" style="gap:28px">
            <form class="panel form" method="post" action="/mi-cuenta/datos" novalidate>
                <?= csrf_field() ?>
                <h2 style="font-size:1.3rem;margin:0">Tus datos</h2>
                <div class="field <?= error('name') ? 'has-error' : '' ?>">
                    <label for="name">Nombre</label>
                    <input class="input" id="name" name="name" value="<?= e(old('name', $user['name'])) ?>" required>
                    <?= partial('partials/field-error', ['name' => 'name']) ?>
                </div>
                <div class="field <?= error('email') ? 'has-error' : '' ?>">
                    <label for="email">Email</label>
                    <input class="input" id="email" name="email" type="email" value="<?= e(old('email', $user['email'])) ?>" required>
                    <?= partial('partials/field-error', ['name' => 'email']) ?>
                </div>
                <div class="form-row">
                    <div class="field"><label for="phone">Teléfono</label><input class="input" id="phone" name="phone" value="<?= e(old('phone', $user['phone'] ?? '')) ?>"></div>
                    <div class="field"><label for="country">País</label><input class="input" id="country" name="country" value="<?= e(old('country', $user['country'] ?? '')) ?>"></div>
                </div>
                <button class="btn" type="submit">Guardar</button>
            </form>

            <form class="panel form" method="post" action="/mi-cuenta/clave" novalidate>
                <?= csrf_field() ?>
                <h2 style="font-size:1.3rem;margin:0">Contraseña</h2>
                <div class="field <?= error('current_password') ? 'has-error' : '' ?>">
                    <label for="current_password">Contraseña actual</label>
                    <input class="input" id="current_password" name="current_password" type="password" autocomplete="current-password" required>
                    <?= partial('partials/field-error', ['name' => 'current_password']) ?>
                </div>
                <div class="field <?= error('password') ? 'has-error' : '' ?>">
                    <label for="new_password">Nueva contraseña</label>
                    <input class="input" id="new_password" name="password" type="password" autocomplete="new-password" minlength="8" required>
                    <?= partial('partials/field-error', ['name' => 'password']) ?>
                </div>
                <div class="field <?= error('password_confirmation') ? 'has-error' : '' ?>">
                    <label for="password_confirmation">Repetila</label>
                    <input class="input" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
                    <?= partial('partials/field-error', ['name' => 'password_confirmation']) ?>
                </div>
                <button class="btn btn--ghost" type="submit">Cambiar contraseña</button>
            </form>

            <form method="post" action="/salir" class="center">
                <?= csrf_field() ?>
                <button class="btn--link" type="submit"><?= icon('logout') ?> Cerrar sesión</button>
            </form>
        </aside>
    </div>
</section>
