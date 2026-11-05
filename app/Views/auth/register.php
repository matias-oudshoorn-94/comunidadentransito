<?php /** Crear cuenta */ ?>
<section class="auth">
    <?= partial('partials/washes') ?>
    <div class="auth__card panel">
        <span class="kicker">Crear cuenta</span>
        <h1>Te damos la bienvenida</h1>
        <p class="muted">Creá tu cuenta y después elegí tu membresía.</p>
        <form class="form" method="post" action="/registro" novalidate>
            <?= csrf_field() ?>
            <div class="field <?= error('name') ? 'has-error' : '' ?>">
                <label for="name">Nombre</label>
                <input class="input" id="name" name="name" value="<?= e(old('name')) ?>" autocomplete="name" required>
                <?= partial('partials/field-error', ['name' => 'name']) ?>
            </div>
            <div class="field <?= error('email') ? 'has-error' : '' ?>">
                <label for="email">Email</label>
                <input class="input" id="email" name="email" type="email" value="<?= e(old('email')) ?>" autocomplete="email" required>
                <?= partial('partials/field-error', ['name' => 'email']) ?>
            </div>
            <div class="field <?= error('password') ? 'has-error' : '' ?>">
                <label for="password">Contraseña</label>
                <input class="input" id="password" name="password" type="password" autocomplete="new-password" minlength="8" required>
                <span class="hint">Al menos 8 caracteres.</span>
                <?= partial('partials/field-error', ['name' => 'password']) ?>
            </div>
            <div class="field <?= error('password_confirmation') ? 'has-error' : '' ?>">
                <label for="password_confirmation">Repetí la contraseña</label>
                <input class="input" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
                <?= partial('partials/field-error', ['name' => 'password_confirmation']) ?>
            </div>
            <button class="btn btn--block" type="submit">Crear cuenta</button>
        </form>
        <hr>
        <p class="small center" style="margin:0">¿Ya tenés cuenta? <a href="/ingresar">Ingresá</a></p>
    </div>
</section>
