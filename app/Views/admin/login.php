<?php /** Ingreso a la administración  @var bool $setup */ ?>
<div class="admin-login">
    <div class="admin-login__card">
        <div class="admin-login__brand">
            <img src="<?= asset('img/logo-mark.svg') ?>" alt="" width="40" height="40">
            <div><strong>Comunidad en Tránsito</strong><span>Administración</span></div>
        </div>

        <?php if ($setup): ?>
            <h1>Crear la cuenta de administración</h1>
            <p class="muted">Todavía no hay ninguna. La que crees ahora va a poder agregar al resto desde el panel.</p>
        <?php else: ?>
            <h1>Ingresar al panel</h1>
            <p class="muted">Este acceso es solo para administración. Las personas de la Comunidad ingresan desde el sitio.</p>
        <?php endif; ?>

        <form class="form" method="post" action="/admin/ingresar" novalidate>
            <?= csrf_field() ?>
            <?php if ($setup): ?>
                <div class="field <?= error('name') ? 'has-error' : '' ?>">
                    <label for="name">Nombre</label>
                    <input class="input" id="name" name="name" value="<?= e(old('name')) ?>" autocomplete="name" required>
                    <?= partial('partials/field-error', ['name' => 'name']) ?>
                </div>
            <?php endif; ?>
            <div class="field <?= error('email') ? 'has-error' : '' ?>">
                <label for="email">Email</label>
                <input class="input" id="email" name="email" type="email" value="<?= e(old('email')) ?>" autocomplete="username" required autofocus>
                <?= partial('partials/field-error', ['name' => 'email']) ?>
            </div>
            <div class="field <?= error('password') ? 'has-error' : '' ?>">
                <label for="password">Contraseña</label>
                <input class="input" id="password" name="password" type="password" autocomplete="<?= $setup ? 'new-password' : 'current-password' ?>" <?= $setup ? 'minlength="10"' : '' ?> required>
                <?php if ($setup): ?><span class="hint">Al menos 10 caracteres.</span><?php endif; ?>
                <?= partial('partials/field-error', ['name' => 'password']) ?>
            </div>
            <?php if ($setup): ?>
                <div class="field <?= error('password_confirmation') ? 'has-error' : '' ?>">
                    <label for="password_confirmation">Repetila</label>
                    <input class="input" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
                    <?= partial('partials/field-error', ['name' => 'password_confirmation']) ?>
                </div>
            <?php endif; ?>
            <button class="btn btn--block" type="submit"><?= $setup ? 'Crear cuenta e ingresar' : 'Ingresar' ?></button>
        </form>
        <a class="admin-login__back" href="/">← Volver al sitio</a>
    </div>
</div>
