<?php
/** B2 · Una sección  @var array $section @var array $entries @var bool $hasAccess @var string $groupName */
$animated = (int) $section['animated_header'] === 1;
?>
<section class="page-hero">
    <?= partial('partials/washes', ['animated' => $animated]) ?>
    <div class="container">
        <nav class="breadcrumb" aria-label="Ruta">
            <a href="/contenidos">Contenidos para vos</a> <span aria-hidden="true">/</span> <span><?= e($groupName) ?></span>
        </nav>
        <span class="kicker"><?= e($section['kicker']) ?></span>
        <h1><?= e($section['name']) ?></h1>
        <p class="lead"><?= e($section['intro']) ?></p>
    </div>
</section>

<?php if ($section['slug'] === 'cafeteria-en-transito'): ?>
    <section class="section--tight" aria-label="Fotos de los encuentros">
        <div class="strip">
            <div class="strip__track">
                <?php foreach ([false, true] as $copy): foreach (config('site.strip_photos') as $p): ?>
                    <div class="strip__item" <?= $copy ? 'aria-hidden="true"' : '' ?>><?= photo($p['src'], $p['alt']) ?></div>
                <?php endforeach; endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<section class="section section--tight">
    <div class="container">
        <?php if (!$hasAccess): ?>
            <div class="access-banner">
                <p><strong>La primera entrega está abierta</strong> para que veas cómo es por dentro. Las demás son para miembros.</p>
                <a class="btn btn--sm" href="/membresias">Ver membresías</a>
            </div>
        <?php endif; ?>

        <?php if ($entries === []): ?>
            <div class="panel panel--sky center"><p class="muted" style="margin:0">Muy pronto vas a encontrar acá las primeras entregas.</p></div>
        <?php else: ?>
            <div class="grid grid--3">
                <?php foreach ($entries as $entry): ?>
                    <?= partial('partials/entry-card', compact('entry', 'section', 'hasAccess')) ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($section['slug'] === 'tecniquitas'): ?>
            <div class="panel panel--sand split" style="margin-top:56px;gap:32px">
                <div>
                    <span class="kicker">Un regalo</span>
                    <h2 style="font-size:1.8rem">Tu primera Tecniquita, gratis</h2>
                    <p class="muted" style="margin:0">Dejame tu email y te la mando para que la pruebes hoy.</p>
                </div>
                <form class="form" method="post" action="/contenidos/tecniquitas/regalo" data-ajax>
                    <?= csrf_field() ?>
                    <div class="field">
                        <label for="gift-email">Tu email</label>
                        <input class="input" id="gift-email" name="email" type="email" required autocomplete="email" placeholder="nombre@email.com">
                    </div>
                    <button class="btn" type="submit">Quiero mi regalo</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</section>
