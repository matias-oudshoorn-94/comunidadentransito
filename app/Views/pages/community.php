<?php
/** A2 · La Comunidad */
$photos = config('site.strip_photos');
?>
<section class="page-hero">
    <?= partial('partials/washes') ?>
    <div class="container container--narrow center">
        <span class="kicker">La Comunidad</span>
        <h1>Escribir en compañía cambia todo</h1>
        <p class="lead">Comunidad en Tránsito nació como un espacio para quienes sienten que la palabra los acompaña, aunque hace rato no le dedican tiempo.</p>
    </div>
</section>

<section class="section--tight" aria-label="Fotos de la comunidad">
    <div class="strip">
        <div class="strip__track">
            <?php foreach ([false, true] as $copy): ?>
                <?php foreach ($photos as $p): ?>
                    <div class="strip__item" <?= $copy ? 'aria-hidden="true"' : '' ?>><?= photo($p['src'], $p['alt']) ?></div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section">
    <div class="container split">
        <div>
            <span class="kicker">Qué es</span>
            <h2>Un tránsito, no un destino</h2>
            <p>No hace falta llegar a ningún lado. La comunidad es un lugar de paso al que volvés cuando lo necesitás: para leer algo lindo, para escribir diez minutos, para encontrarte con otras personas que también están en camino.</p>
            <p>Cada semana suben contenidos nuevos, y una vez por mes nos vemos en la Cafetería en tránsito para leernos en vivo.</p>
            <span class="hand">sin apuro, sin exigencias</span>
        </div>
        <div class="grid grid--2 values">
            <div class="card"><span class="card__icon"><?= icon('leaf') ?></span><h3>A tu ritmo</h3><p class="muted">Todo queda grabado y disponible.</p></div>
            <div class="card"><span class="card__icon"><?= icon('heart') ?></span><h3>Con cuidado</h3><p class="muted">Un espacio amable para compartir.</p></div>
            <div class="card"><span class="card__icon"><?= icon('pen') ?></span><h3>Para crear</h3><p class="muted">Consignas que destraban la escritura.</p></div>
            <div class="card"><span class="card__icon"><?= icon('wave') ?></span><h3>Cerca del mar</h3><p class="muted">Con la calma como brújula.</p></div>
        </div>
    </div>
</section>

<section class="section section--white">
    <div class="container">
        <div class="section__head">
            <span class="kicker">Lo que vas a encontrar</span>
            <h2>Tres maneras de estar</h2>
        </div>
        <div class="grid grid--3">
            <?php foreach ($groups as $key => $sections): ?>
                <div class="panel panel--sky">
                    <h3><?= e(config("site.groups.$key")) ?></h3>
                    <ul class="list-plain">
                        <?php foreach ($sections as $s): ?>
                            <li><a class="list-row" href="/contenidos/<?= e($s['slug']) ?>"><span><?= e($s['name']) ?></span><?= icon('arrow') ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section--tight">
    <div class="container">
        <div class="cta-band">
            <span class="wash wash--light" aria-hidden="true"></span>
            <h2>¿Te sumás?</h2>
            <p class="lead" style="margin-inline:auto">Elegí tu membresía y empezá hoy.</p>
            <div class="actions"><a class="btn" href="/membresias">Ver membresías</a></div>
        </div>
    </div>
</section>
