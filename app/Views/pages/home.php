<?php
/** A1 · Portada  @var array $groups @var array $periods @var array $faq */
$groupNames = config('site.groups');
?>
<section class="home-hero">
    <span class="wash wash--light" aria-hidden="true"></span>
    <span class="wash wash--sand" aria-hidden="true"></span>
    <div class="container split">
        <div>
            <span class="kicker">Comunidad de escritura y lectura</span>
            <h1>Un lugar para crear, leer y <em>encontrarnos</em> sin apuro.</h1>
            <p class="lead">Consignas, lecturas, grabaciones y encuentros en vivo para que la palabra vuelva a ser un espacio tuyo. A tu ritmo, en compañía.</p>
            <div class="actions">
                <a class="btn" href="/membresias">Sumarme a la comunidad <?= icon('arrow', 'icon icon--move') ?></a>
                <a class="btn btn--ghost" href="/contenidos">Ver los contenidos</a>
            </div>
            <div class="trust">
                <div class="trust__dots" aria-hidden="true"><span></span><span></span><span></span></div>
                <span>Contenido nuevo cada semana · Encuentros en vivo cada mes</span>
            </div>
        </div>
        <div class="home-hero__photo">
            <div class="fader" data-fader>
                <?php foreach (config('site.hero_photos') as $i => $p): ?>
                    <?= photo($p['src'], $p['alt'], $i === 0 ? 'is-active' : '') ?>
                <?php endforeach; ?>
            </div>
            <div class="home-hero__note"><span class="hand">me encanta leerte</span></div>
        </div>
    </div>
</section>

<section class="section section--white">
    <div class="container">
        <div class="section__head">
            <span class="kicker">Cómo funciona</span>
            <h2>Tres pasos para empezar</h2>
        </div>
        <div class="grid grid--3 steps">
            <div class="step"><h3>Elegí tu membresía</h3><p>Orilla para asomarte o Inmersión para vivir la comunidad completa.</p></div>
            <div class="step"><h3>Entrá cuando quieras</h3><p>Todo queda en Mi Comunidad: lecturas, consignas y grabaciones, siempre a mano.</p></div>
            <div class="step"><h3>Escribí y compartí</h3><p>Una runa por día, encuentros en vivo y una comunidad que te lee con cariño.</p></div>
        </div>
    </div>
</section>

<section class="section has-washes">
    <span class="wash wash--light" style="top:10%;right:-200px" aria-hidden="true"></span>
    <div class="container">
        <div class="section__head">
            <span class="kicker">Contenidos para vos</span>
            <h2>Explorá lo que hay dentro</h2>
            <p class="lead">Cada sección tiene un adelanto para que veas cómo es por dentro.</p>
        </div>

        <div class="filters" data-filters="explorar" role="group" aria-label="Filtrar contenidos">
            <button class="filter" type="button" data-filter="todo" aria-pressed="true">Todo</button>
            <?php foreach ($groupNames as $key => $name): ?>
                <button class="filter" type="button" data-filter="<?= e($key) ?>" aria-pressed="false"><?= e($name) ?></button>
            <?php endforeach; ?>
        </div>

        <div class="grid grid--3" id="explorar">
            <?php foreach ($groups as $sections): ?>
                <?php foreach ($sections as $section): ?>
                    <?= partial('partials/section-card', ['section' => $section]) ?>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section--sand">
    <div class="container quote">
        <blockquote>“Escribir es una forma de cuidarnos: de mirar con atención y de nombrar lo que sentimos.”</blockquote>
        <span class="hand">Sil</span>
    </div>
</section>

<section class="section section--white" id="planes">
    <div class="container">
        <div class="section__head center">
            <span class="kicker">Membresías</span>
            <h2>Elegí cómo querés estar</h2>
            <p class="lead">Podés cambiar de plan o darte de baja cuando quieras desde Mi cuenta.</p>
        </div>
        <?= partial('partials/plans', ['periods' => $periods]) ?>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section__head center">
            <span class="kicker">Preguntas frecuentes</span>
            <h2>Lo que suelen preguntarme</h2>
        </div>
        <?= partial('partials/faq', ['faq' => $faq]) ?>
    </div>
</section>

<section class="section section--tight">
    <div class="container">
        <div class="cta-band">
            <span class="wash wash--light" aria-hidden="true"></span>
            <span class="kicker">Te espero</span>
            <h2>Hay un lugar para vos en la mesa</h2>
            <p class="lead" style="margin-inline:auto">Sumate hoy y empezá con la runa del día.</p>
            <div class="actions"><a class="btn" href="/membresias">Ver membresías <?= icon('arrow', 'icon icon--move') ?></a></div>
        </div>
    </div>
</section>
