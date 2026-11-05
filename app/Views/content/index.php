<?php
/** B1 · Contenidos para vos  @var array $groups @var array $tiers */
$groupNames = config('site.groups');
?>
<section class="page-hero">
    <?= partial('partials/washes') ?>
    <div class="container container--narrow center">
        <span class="kicker">Contenidos para vos</span>
        <h1>Todo lo que pasa en la comunidad</h1>
        <p class="lead">Crear y escribir, leer, ver y escuchar, y encontrarnos. Cada sección te muestra un adelanto.</p>
    </div>
</section>

<section class="section section--tight" style="padding-top:0">
    <div class="container">
        <?php if ($tiers === []): ?>
            <div class="access-banner">
                <p><strong>Estás viendo los adelantos.</strong> Con una membresía se abre el contenido completo de cada sección.</p>
                <a class="btn btn--sm" href="/membresias">Ver membresías</a>
            </div>
        <?php endif; ?>

        <div class="filters" data-filters="contenidos" role="group" aria-label="Filtrar secciones">
            <button class="filter" type="button" data-filter="todo" aria-pressed="true">Todo</button>
            <?php foreach ($groupNames as $key => $name): ?>
                <button class="filter" type="button" data-filter="<?= e($key) ?>" aria-pressed="false"><?= e($name) ?></button>
            <?php endforeach; ?>
        </div>

        <div class="grid grid--3" id="contenidos">
            <?php foreach ($groups as $sections): ?>
                <?php foreach ($sections as $section): ?>
                    <?= partial('partials/section-card', [
                        'section'  => $section,
                        'unlocked' => array_intersect($tiers, $section['tiers']) !== [],
                    ]) ?>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
