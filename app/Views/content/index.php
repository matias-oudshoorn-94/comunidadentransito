<?php /** B1 · Contenidos para vos  @var array $groups @var array $tiers @var string $fromPrice */
$heads = ['crear' => ['Crear y escribir', 'Para crear y escribir'], 'leer' => ['Leer, ver y escuchar', 'Para leer, ver y escuchar'], 'encontrarnos' => ['Encontrarnos', 'Para encontrarnos']];
?>
<section class="hero">
    <?= partial('partials/washes') ?>
    <div class="container">
        <span class="kicker">Contenidos</span>
        <span class="hand">para disfrutar cuando quieras</span>
        <h1 style="max-width:12em">Todo lo que hay en la Comunidad</h1>
        <p class="lead">Cada mes se suman entregas nuevas. Los visitantes ven un adelanto; los miembros, todo.</p>
        <div class="filters" data-filters="contenidos" data-filter-blocks role="group" aria-label="Filtrar" style="margin-top:24px">
            <button class="filter" type="button" data-filter="todo" aria-pressed="true">Todo</button>
            <?php foreach (config('site.groups') as $key => $name): ?>
                <button class="filter" type="button" data-filter="<?= e($key) ?>" aria-pressed="false"><?= e($name) ?></button>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<div id="contenidos">
    <?php $i = 0; foreach ($groups as $key => $sections): ?>
        <section class="band <?= $i++ % 2 ? 'band--white' : '' ?>" id="<?= e($key) ?>" data-group="<?= e($key) ?>" style="<?= $i === 1 ? 'padding-top:0' : '' ?>">
            <div class="container">
                <div class="band__head"><span class="kicker"><?= e($heads[$key][0]) ?></span><h2><?= e($heads[$key][1]) ?></h2></div>
                <div class="grid <?= count($sections) > 3 ? 'grid--4' : 'grid--3' ?>" style="align-items:start">
                    <?php foreach ($sections as $section): ?>
                        <?= partial('partials/section-card', ['section' => $section, 'open' => array_intersect($tiers, $section['tiers']) !== []]) ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endforeach; ?>
</div>

<?php if ($tiers === []): ?>
    <?= partial('partials/cta', ['hand' => 'todo esto te está esperando', 'title' => 'Sumate para ver todo', 'note' => 'Desde ' . $fromPrice . ' por mes · Cancelás cuando quieras', 'button' => 'Ver membresías', 'bg' => 'sky']) ?>
<?php endif; ?>
