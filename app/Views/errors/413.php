<section class="error-page">
    <?= partial('partials/washes') ?>
    <div class="container container--narrow" style="position:relative">
        <span class="hand">uy…</span>
        <h1>El archivo es demasiado grande</h1>
        <p class="lead" style="margin-inline:auto"><?= e($message ?? '') ?> Volvé atrás y probá con un archivo más liviano, o usá un link de YouTube.</p>
        <div class="actions" style="justify-content:center"><a class="btn" href="javascript:history.back()">Volver</a></div>
    </div>
</section>
