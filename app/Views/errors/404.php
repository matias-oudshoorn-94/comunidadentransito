<section class="error-page">
    <?= partial('partials/washes', ['animated' => true]) ?>
    <div class="container container--narrow" style="position:relative">
        <span class="hand">uy…</span>
        <h1>No encontramos esta página</h1>
        <p class="lead" style="margin-inline:auto"><?= 404 === 419 ? 'Volvé a la página anterior, recargala e intentá de nuevo.' : 'Volvé al inicio o escribinos si necesitás ayuda.' ?></p>
        <div class="actions" style="justify-content:center"><a class="btn" href="/">Ir al inicio</a><a class="btn btn--ghost" href="/contacto">Contacto</a></div>
    </div>
</section>
