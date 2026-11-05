<?php /** Crear cuenta (sin membresía todavía) */ ?>
<section class="auth">
    <?= partial('partials/washes') ?>
    <div class="auth__card">
        <img class="auth__logo" src="<?= asset('img/logo-sintetico.webp') ?>" alt="Comunidad en tránsito">
        <span class="hand">así te damos la bienvenida</span>
        <h1>Creá tu cuenta</h1>
        <form class="form" method="post" action="/registro" novalidate>
            <?= csrf_field() ?>
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
            <div class="field <?= error('password_confirmation') ? 'has-error' : '' ?>">
                <label for="password_confirmation">Repetila</label>
                <input class="input" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
                <?= partial('partials/field-error', ['name' => 'password_confirmation']) ?>
            </div>
            <button class="btn btn--block btn--lg" type="submit">Crear cuenta</button>
        </form>
        <hr>
        <div class="auth__alt"><p>¿Ya tenés cuenta?</p><a class="link-arrow" href="/ingresar">Ingresá →</a></div>
    </div>
</section>
