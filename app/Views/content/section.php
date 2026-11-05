<?php
/** B2 · Una sección
 * @var array $section @var array $entries @var bool $hasAccess @var array $cfg @var string $groupName */
$slug = $section['slug'];
$crumbs = [['Contenidos para vos', '/contenidos'], [$section['name'], null]];
if ($slug === 'pispeando-arte') {
    $crumbs = [['Contenidos', '/contenidos'], ['Crear', '/contenidos#crear'], ['Pispeando Arte', null]];
}
$card = fn (array $entry) => partial('partials/entry-card', ['entry' => $entry, 'section' => $section, 'hasAccess' => $hasAccess]);
?>
<?= partial('partials/hero', [
    'crumbs' => $crumbs, 'kicker' => $slug === 'pispeando-arte' ? 'CREAR' : null, 'hand' => $cfg['hand'] ?? null,
    'title' => $section['name'], 'text' => $section['intro'], 'image' => $cfg['image'] ?? null,
    'frame' => $cfg['frame'] ?? null, 'caption' => $cfg['image_caption'] ?? null,
    'facts' => $cfg['chips'] ?? null, 'animated' => (int) $section['animated_header'] === 1,
]) ?>

<?php if (!empty($cfg['strip'])): ?>
    <div class="strip" aria-label="Fotos" style="margin-top:-40px;margin-bottom:24px">
        <div class="strip__track">
            <?php foreach ([false, true] as $copy): foreach ($cfg['strip'] as $src): ?>
                <div class="strip__item" <?= $copy ? 'aria-hidden="true"' : '' ?>><?= img($src, '', 'loading="lazy"') ?></div>
            <?php endforeach; endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<?php if ($slug === 'tecniquitas'): ?>
    <section class="band band--white">
        <div class="container big-quote">
            <span class="kicker">¿Por qué Tecniquitas?</span>
            <blockquote>Porque la experiencia es algo que sucede, real; con la práctica y la reiteración logramos organizar su ritmo y sus pasos. Pasa de ser una experiencia desbocada a constituir una técnica, con melodía y armonía. Y cuando la técnica se incorpora y se afianza, se convierte en destreza, la cual con el tiempo, ¡nos libera!</blockquote>
            <span class="hand">— Silvina</span>
        </div>
    </section>
    <section class="band band--sky" id="regalo">
        <div class="container hero__grid" style="align-items:center">
            <div>
                <span class="kicker">Tu primera Tecniquita es de regalo</span>
                <span class="hand">un regalo para vos</span>
                <h2>Probala en casa, sin compromiso</h2>
                <p class="muted">Dejá tu mail y te mandamos esta propuesta completa, con su lámina para imprimir. Si te gusta, en la Comunidad tenés una nueva cada mes.</p>
                <form class="gift-form" method="post" action="/contenidos/tecniquitas/regalo" data-ajax style="max-width:420px;margin-top:20px">
                    <?= csrf_field() ?>
                    <label class="visually-hidden" for="gift-email">Tu email</label>
                    <input class="input" id="gift-email" name="email" type="email" required autocomplete="email" placeholder="Tu email">
                    <button class="btn btn--block" type="submit">Quiero recibirla</button>
                    <span class="note">Podés darte de baja cuando quieras.</span>
                </form>
            </div>
            <div class="gift-card">
                <span class="pill"><?= icon('gift', 'icon') ?> De regalo</span>
                <div style="margin-top:16px"><span class="icon-dot"><?= icon('mail') ?></span></div>
                <h3>Una tecniquita sorpresa</h3>
                <p>Una propuesta creativa para hacer en casa. Te llega por mail y la descubrís al abrirlo.</p>
                <ul>
                    <?php foreach (['La consigna completa, paso a paso', 'Una lámina para imprimir', 'Para hacer durante una semana, a tu ritmo'] as $li): ?>
                        <li><span class="icon-dot"><?= icon('check') ?></span><?= e($li) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php
// Bloques de entregas: uno por defecto, o varios por categoría (Materiales para ver)
$blocks = $cfg['blocks'] ?? [[null, ...($cfg['block'] ?? ['PARA MIEMBROS', 'Las entregas', null]), $cfg['columns'] ?? 3]];
foreach ($blocks as $b => [$category, $kicker, $title, $text, $cols]):
    $list = $category ? array_values(array_filter($entries, fn ($e) => $e['category'] === $category)) : $entries;
?>
    <section class="band <?= $b % 2 ? 'band--white' : '' ?>" style="<?= $b === 0 && empty($cfg['strip']) && $slug !== 'tecniquitas' ? 'padding-top:24px' : '' ?>">
        <div class="container">
            <div class="band__head <?= ($cfg['layout'] ?? '') === 'featured' ? 'band__head--row' : '' ?>">
                <div>
                    <span class="kicker"><?= e($kicker) ?></span>
                    <h2><?= e($title) ?></h2>
                    <?php if ($text): ?><p><?= e($text) ?></p><?php endif; ?>
                </div>
                <?php if (($cfg['layout'] ?? '') === 'featured'): $years = array_unique(array_map(fn ($e) => substr($e['published_at'], 0, 4), $entries)); rsort($years); ?>
                    <div class="filters" data-filters="entregas-<?= $b ?>" data-filter-attr="year" role="group" aria-label="Filtrar por año">
                        <button class="filter" type="button" data-filter="todo" aria-pressed="true">Todas</button>
                        <?php foreach ($years as $y): ?><button class="filter" type="button" data-filter="<?= e($y) ?>" aria-pressed="false"><?= e($y) ?></button><?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($cfg['filters'])): ?>
                <div class="filters" data-filters="entregas-<?= $b ?>" role="group" aria-label="Filtrar por tema" style="margin:-8px 0 24px">
                    <button class="filter" type="button" data-filter="todo" aria-pressed="true">Todo</button>
                    <?php foreach ($cfg['filters'] as $key => $label): ?><button class="filter" type="button" data-filter="<?= e($key) ?>" aria-pressed="false"><?= e($label) ?></button><?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($list === []): ?>
                <p class="muted">Muy pronto vas a encontrar acá las primeras entregas.</p>
            <?php elseif (($cfg['layout'] ?? '') === 'featured'): $first = array_shift($list); $open = $hasAccess || $first['is_preview']; ?>
                <div id="entregas-<?= $b ?>" style="display:grid;gap:20px">
                    <article class="card card--feature" data-year="<?= e(substr($first['published_at'], 0, 4)) ?>">
                        <a class="card__media" href="/contenidos/<?= e($slug) ?>/<?= e($first['slug']) ?>" tabindex="-1" aria-hidden="true"><?= img($first['image'], $first['title']) ?></a>
                        <div class="card__body">
                            <div style="display:flex;gap:10px;align-items:center"><span class="pill">Nueva</span><span class="kicker"><?= e(preg_replace('/^PISPEANDO ARTE · /', '', (string) $first['kicker'])) ?></span></div>
                            <h3><a href="/contenidos/<?= e($slug) ?>/<?= e($first['slug']) ?>"><?= e($first['title']) ?></a></h3>
                            <p><?= e($first['excerpt']) ?></p>
                            <?php if ($first['hand_note']): ?><span class="hand" style="font-size:1.3rem;margin:0"><?= e($first['hand_note']) ?></span><?php endif; ?>
                            <div class="card__actions"><a class="btn" href="/contenidos/<?= e($slug) ?>/<?= e($first['slug']) ?>"><?= e($cfg['action']) ?></a></div>
                        </div>
                    </article>
                    <div class="grid grid--2">
                        <?php foreach ($list as $e): ?>
                            <article class="card card--row" data-year="<?= e(substr($e['published_at'], 0, 4)) ?>">
                                <a class="card__media" href="/contenidos/<?= e($slug) ?>/<?= e($e['slug']) ?>" tabindex="-1" aria-hidden="true"><?= img($e['image'], $e['title']) ?></a>
                                <div class="card__body">
                                    <span class="kicker"><?= e(preg_replace('/^PISPEANDO ARTE · /', '', (string) $e['kicker'])) ?></span>
                                    <h3><a href="/contenidos/<?= e($slug) ?>/<?= e($e['slug']) ?>"><?= e($e['title']) ?></a></h3>
                                    <p><?= e($e['excerpt']) ?></p>
                                    <span class="lock-line"><a href="/contenidos/<?= e($slug) ?>/<?= e($e['slug']) ?>">Leer →</a></span>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="grid grid--<?= (int) $cols ?>" id="entregas-<?= $b ?>" style="align-items:start">
                    <?php foreach ($list as $entry): ?><?= $card($entry) ?><?php endforeach; ?>
                    <?php if ($slug === 'tecniquitas'): ?>
                        <article class="card">
                            <a class="card__media" href="#regalo" tabindex="-1" aria-hidden="true"><?= img('fotos/papeles-pasto.jpg', 'Tecniquita de regalo') ?></a>
                            <div class="card__body">
                                <span class="kicker">Septiembre 2026 · Lámina</span>
                                <h3><a href="#regalo">Tecniquita de regalo</a></h3>
                                <p>Una propuesta sorpresa que te llega por mail.</p>
                                <span class="lock-line"><a href="#regalo">Disponible gratis →</a></span>
                            </div>
                        </article>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
<?php endforeach; ?>

<?php if (!empty($cfg['promo']) && ($promo = \App\Models\Section::bySlug($cfg['promo']))): $promoCfg = config('sections.' . $cfg['promo']); ?>
    <section class="band band--white">
        <div class="container">
            <div class="promo">
                <?= img('libros/habitante-de-la-vida.jpg', 'Habitante de la Vida', '') ?>
                <div>
                    <span class="kicker"><?= e($promo['name']) ?></span>
                    <h2>Capítulos de sus libros</h2>
                    <p><?= e($promo['intro']) ?></p>
                    <a class="link-arrow" href="/contenidos/<?= e($promo['slug']) ?>">Ir a <?= e($promo['name']) ?> →</a>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php if (!$hasAccess && !empty($cfg['cta'])): [$h, $t, $btn, $bg] = $cfg['cta']; ?>
    <?= partial('partials/cta', ['hand' => $h, 'title' => $t, 'button' => $btn, 'bg' => $bg]) ?>
<?php endif; ?>
