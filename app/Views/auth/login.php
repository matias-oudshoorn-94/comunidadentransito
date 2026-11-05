<?php /** C2 · Ingresar */ ?>
<section class="auth">
    <?= partial('partials/washes') ?>
    <div class="auth__card panel">
        <span class="kicker">Ingresar</span>
        <h1>Qué bueno verte</h1>
        <p class="muted">Entrá con el email de tu membresía.</p>
        <form class="form" method="post" action="/ingresar" novalidate>
            <?= csrf_field() ?>
            <div class="field <?= error('email') ? 'has-error' : '' ?>">
                <label for="email">Email</label>
                <input class="input" id="email" name="email" type="email" value="<?= e(old('email')) ?>" autocomplete="email" required autofocus>
                <?= partial('partials/field-error', ['name' => 'email']) ?>
            </div>
            <div class="field <?= error('password') ? 'has-error' : '' ?>">
                <label for="password">Contraseña</label>
                <input class="input" id="password" name="password" type="password" autocomplete="current-password" required>
                <?= partial('partials/field-error', ['name' => 'password']) ?>
            </div>
            <button class="btn btn--block" type="submit">Ingresar</button>
        </form>
        <hr>
        <p class="small center" style="margin:0">¿Todavía no sos parte? <a href="/membresias">Elegí tu membresía</a> o <a href="/registro">creá una cuenta</a>.</p>
        <p class="small center muted" style="margin:8px 0 0">¿Olvidaste tu contraseña? <a href="/contacto">Escribinos</a> y te ayudamos.</p>
    </div>
</section>
