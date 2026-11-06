<?php
/** @var array $user @var array|null $current @var array $memberships @var array $payments @var array $plans */
?>
<p><a href="/admin/usuarios">← Volver a usuarios</a></p>

<div class="cols cols--wide">
    <div style="display:grid;gap:24px;min-width:0">
        <section class="box">
            <div class="box__head">
                <h2>Membresía actual</h2>
                <?= $current ? badge('active') : '<span class="badge">Sin membresía activa</span>' ?>
            </div>
            <?php if ($current): ?>
                <dl class="dl">
                    <dt>Plan</dt><dd><?= e($current['plan_name']) ?></dd>
                    <dt>Desde</dt><dd><?= fecha($current['starts_at']) ?></dd>
                    <dt>Vence</dt><dd><?= fecha($current['ends_at']) ?></dd>
                    <dt>Renovación</dt><dd><?= $current['auto_renew'] ? 'Automática con ' . e(medio($current['gateway'])) : 'Manual' ?></dd>
                </dl>
            <?php endif; ?>
        </section>

        <section class="box">
            <div class="box__head"><h2>Historial de membresías</h2></div>
            <div class="table-wrap">
                <table class="atable">
                    <thead><tr><th>Plan</th><th>Período</th><th>Medio</th><th>Estado</th><th>Acciones</th></tr></thead>
                    <tbody>
                        <?php foreach ($memberships as $m): ?>
                            <tr>
                                <td><?= e($m['plan_name']) ?></td>
                                <td><?= fecha_corta($m['starts_at']) ?> → <?= fecha_corta($m['ends_at']) ?></td>
                                <td><?= e(medio($m['gateway'])) ?><?= $m['auto_renew'] ? '<span class="sub">se renueva</span>' : '' ?></td>
                                <td><?= badge($m['status']) ?></td>
                                <td>
                                    <form class="inline-form" method="post" action="/admin/membresias/<?= (int) $m['id'] ?>">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="extend">
                                        <select class="select" name="months" aria-label="Meses"><option value="1">+1 mes</option><option value="3">+3 meses</option><option value="6">+6 meses</option><option value="12">+12 meses</option></select>
                                        <button class="btn btn--soft btn--xs" type="submit">Extender</button>
                                    </form>
                                    <?php if ($m['status'] === 'active'): ?>
                                        <form class="inline-form" method="post" action="/admin/membresias/<?= (int) $m['id'] ?>" data-confirm="¿Cortar el acceso ahora? La persona deja de ver los contenidos.">
                                            <?= csrf_field() ?><input type="hidden" name="action" value="revoke">
                                            <button class="btn btn--ghost btn--xs" type="submit">Cortar acceso</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$memberships): ?><tr><td colspan="5" class="empty">Sin membresías.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="box">
            <div class="box__head"><h2>Pagos</h2></div>
            <div class="table-wrap">
                <table class="atable">
                    <thead><tr><th>Fecha</th><th>Plan</th><th>Medio</th><th class="num">Monto</th><th>Estado</th></tr></thead>
                    <tbody>
                        <?php foreach ($payments as $p): ?>
                            <tr>
                                <td><?= fecha_corta($p['paid_at'] ?? $p['created_at']) ?></td>
                                <td><?= e($p['plan_name']) ?><?= $p['note'] ? '<span class="sub">' . e($p['note']) . '</span>' : '' ?></td>
                                <td><?= e(medio($p['gateway'])) ?></td>
                                <td class="num"><?= money($p['amount'], $p['currency']) ?></td>
                                <td><?= badge($p['status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$payments): ?><tr><td colspan="5" class="empty">Sin pagos.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <div style="display:grid;gap:24px">
        <form class="box form" method="post" action="/admin/usuarios/<?= (int) $user['id'] ?>" novalidate>
            <?= csrf_field() ?>
            <div class="box__head" style="margin:0"><h2>Datos</h2><span class="small muted">Alta: <?= fecha_corta($user['created_at']) ?></span></div>
            <div class="field <?= error('name') ? 'has-error' : '' ?>"><label for="name">Nombre</label><input class="input" id="name" name="name" value="<?= e(old('name', $user['name'])) ?>"><?= partial('partials/field-error', ['name' => 'name']) ?></div>
            <div class="field <?= error('email') ? 'has-error' : '' ?>"><label for="email">Email</label><input class="input" id="email" name="email" type="email" value="<?= e(old('email', $user['email'])) ?>"><?= partial('partials/field-error', ['name' => 'email']) ?></div>
            <div class="form-row">
                <div class="field"><label for="phone">Teléfono</label><input class="input" id="phone" name="phone" value="<?= e($user['phone']) ?>"></div>
                <div class="field"><label for="country">País</label><input class="input" id="country" name="country" value="<?= e($user['country']) ?>"></div>
            </div>
            <div class="field"><label for="notes">Notas internas</label><textarea class="textarea" id="notes" name="notes" style="min-height:90px"><?= e($user['notes']) ?></textarea></div>
            <div class="field <?= error('password') ? 'has-error' : '' ?>"><label for="password">Nueva contraseña</label><input class="input" id="password" name="password" type="password" autocomplete="new-password" placeholder="Dejar vacío para no cambiarla"><?= partial('partials/field-error', ['name' => 'password']) ?></div>
            <button class="btn" type="submit">Guardar</button>
        </form>

        <form class="box form" method="post" action="/admin/usuarios/<?= (int) $user['id'] ?>/membresia" novalidate>
            <?= csrf_field() ?>
            <h2 style="font-size:1.2rem;margin:0">Asignar membresía</h2>
            <p class="small muted" style="margin:0">Para pagos por transferencia, regalos o becas.</p>
            <div class="field"><label for="plan_id">Plan</label>
                <select class="select" id="plan_id" name="plan_id">
                    <?php foreach ($plans as $p): ?><option value="<?= (int) $p['id'] ?>"><?= e($p['name']) ?> · <?= money($p['price_ars']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="field"><label for="starts_at">Desde</label><input class="input" id="starts_at" name="starts_at" type="date" value="<?= date('Y-m-d') ?>"></div>
            <div class="field"><label for="note">Nota</label><input class="input" id="note" name="note" placeholder="Ej.: transferencia del 3/11"></div>
            <label class="check"><input type="checkbox" name="record_payment" value="1" checked> Registrar el pago en pesos</label>
            <button class="btn btn--soft" type="submit">Asignar</button>
        </form>
    </div>
</div>
