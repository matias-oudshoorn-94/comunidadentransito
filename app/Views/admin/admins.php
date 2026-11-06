<?php /** @var array $admins @var int $me */ ?>
<div class="notice"><?= icon('lock') ?><span>Estas cuentas entran por <strong><?= e(absolute_url('/admin/ingresar')) ?></strong>, que es distinto del ingreso de la Comunidad. Son independientes de las personas del sitio.</span></div>

<div class="cols cols--wide">
    <section class="box">
        <div class="box__head"><h2>Cuentas de administración</h2><span class="small muted"><?= count($admins) ?></span></div>
        <div style="display:grid;gap:4px">
            <?php foreach ($admins as $a): $mine = (int) $a['id'] === $me; ?>
                <details class="admin-row">
                    <summary>
                        <span class="admin-row__who"><strong><?= e($a['name']) ?></strong><?= $mine ? ' <span class="badge badge--plain">Vos</span>' : '' ?><span class="sub"><?= e($a['email']) ?></span></span>
                        <span class="sub">Último ingreso: <?= $a['last_login_at'] ? fecha($a['last_login_at'], true) : 'nunca' ?></span>
                        <span class="btn btn--ghost btn--xs">Editar</span>
                    </summary>
                    <form class="form admin-row__form" method="post" action="/admin/administradores/<?= (int) $a['id'] ?>" novalidate>
                        <?= csrf_field() ?>
                        <div class="form-row">
                            <div class="field"><label>Nombre</label><input class="input" name="name" value="<?= e($a['name']) ?>" required></div>
                            <div class="field"><label>Email</label><input class="input" name="email" type="email" value="<?= e($a['email']) ?>" required></div>
                        </div>
                        <div class="field"><label>Nueva contraseña</label><input class="input" name="password" type="password" autocomplete="new-password" placeholder="Dejar vacío para no cambiarla"></div>
                        <div class="actions">
                            <button class="btn btn--sm" type="submit">Guardar</button>
                        </div>
                    </form>
                    <?php if (!$mine): ?>
                        <form class="admin-row__form" method="post" action="/admin/administradores/<?= (int) $a['id'] ?>/quitar" data-confirm="¿Quitarle el acceso a <?= e($a['name']) ?>? No va a poder entrar más al panel.">
                            <?= csrf_field() ?><button class="btn btn--ghost btn--xs" type="submit">Quitar acceso</button>
                        </form>
                    <?php endif; ?>
                </details>
            <?php endforeach; ?>
        </div>
    </section>

    <form class="box form" method="post" action="/admin/administradores" novalidate>
        <?= csrf_field() ?>
        <h2 style="font-size:1.2rem;margin:0">Agregar administrador</h2>
        <div class="field <?= error('name') ? 'has-error' : '' ?>"><label for="n-name">Nombre</label><input class="input" id="n-name" name="name" value="<?= e(old('name')) ?>" required><?= partial('partials/field-error', ['name' => 'name']) ?></div>
        <div class="field <?= error('email') ? 'has-error' : '' ?>"><label for="n-email">Email</label><input class="input" id="n-email" name="email" type="email" value="<?= e(old('email')) ?>" required><?= partial('partials/field-error', ['name' => 'email']) ?></div>
        <div class="field <?= error('password') ? 'has-error' : '' ?>"><label for="n-pass">Contraseña</label><input class="input" id="n-pass" name="password" type="password" autocomplete="new-password" minlength="10" required><span class="hint">Al menos 10 caracteres. Pasásela por un medio seguro.</span><?= partial('partials/field-error', ['name' => 'password']) ?></div>
        <button class="btn" type="submit">Agregar</button>
    </form>
</div>
