<?php /** C2 · Ingresar */ ?>
<section class="auth">
    <?= partial('partials/washes') ?>
    <div class="auth__card">
        <img class="auth__logo" src="<?= asset('img/logo-sintetico.webp') ?>" alt="Comunidad en tránsito">
        <span class="hand">qué lindo que vuelvas</span>
        <h1>Ingresá a la Comunidad</h1>
        <form class="form" method="post" action="/ingresar" novalidate>
            <?= csrf_field() ?>
            <div class="field <?= error('email') ? 'has-error' : '' ?>">
                <label for="email">Email</label>
                <input class="input" id="email" name="email" type="email" value="<?= e(old('email')) ?>" autocomplete="email" placeholder="nombre@correo.com" required autofocus>
                <?= partial('partials/field-error', ['name' => 'email']) ?>
            </div>
            <div class="field <?= error('password') ? 'has-error' : '' ?>">
                <label for="password">Contraseña</label>
                <input class="input" id="password" name="password" type="password" autocomplete="current-password" placeholder="••••••••" required>
                <?= partial('partials/field-error', ['name' => 'password']) ?>
            </div>
            <a class="auth__forgot" href="<?= e(whatsapp('Hola Silvina, olvidé mi contraseña de la Comunidad.')) ?>" target="_blank" rel="noopener">¿Olvidaste tu contraseña?</a>
            <button class="btn btn--block btn--lg" type="submit">Ingresar</button>
        </form>
        <hr>
        <div class="auth__alt"><p>¿Todavía no sos parte?</p><a class="link-arrow" href="/membresias">Sumate a la Comunidad →</a></div>
    </div>
</section>
