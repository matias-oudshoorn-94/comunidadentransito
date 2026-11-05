<?php /** A5 · Sobre Silvina */ ?>
<section class="page-hero">
    <?= partial('partials/washes') ?>
    <div class="container split">
        <div>
            <span class="kicker">Sobre Silvina</span>
            <h1>Hola, soy Sil</h1>
            <p class="lead">Escritora y tallerista. Acompaño a personas que quieren reencontrarse con la escritura.</p>
            <p>Creo que escribir es una forma de cuidarnos: de mirar con atención, de nombrar lo que sentimos y de dejar huella. Por eso armé esta comunidad, para que nadie tenga que hacerlo en soledad.</p>
            <p>Acá vas a encontrar lo que más me gusta compartir: consignas que despiertan, lecturas que acompañan, encuentros que abrazan.</p>
            <div class="signature">Sil</div>
        </div>
        <div class="about-photo"><?= photo('fotos/sil-retrato.jpg', 'Retrato de Silvina') ?></div>
    </div>
</section>

<section class="section section--white">
    <div class="container">
        <div class="grid grid--3">
            <div class="card values" style="padding:28px;gap:10px"><span class="card__icon"><?= icon('book') ?></span><h3>Libros</h3><p class="muted">Novelas, cuentos y cuadernos de escritura.</p><a href="/libros">Ver libros →</a></div>
            <div class="card values" style="padding:28px;gap:10px"><span class="card__icon"><?= icon('pen') ?></span><h3>Talleres</h3><p class="muted">Grupos de escritura para animarse a la palabra.</p></div>
            <div class="card values" style="padding:28px;gap:10px"><span class="card__icon"><?= icon('wave') ?></span><h3>El mar</h3><p class="muted">La calma y el ir y venir de las olas como brújula.</p></div>
        </div>
    </div>
</section>

<section class="section section--tight">
    <div class="container">
        <div class="cta-band">
            <span class="wash wash--light" aria-hidden="true"></span>
            <h2>Escribamos en compañía</h2>
            <div class="actions"><a class="btn" href="/membresias">Sumarme</a><a class="btn btn--ghost" href="/contacto">Escribirme</a></div>
        </div>
    </div>
</section>
