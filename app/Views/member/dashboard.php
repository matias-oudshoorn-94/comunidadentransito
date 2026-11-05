<?php
/** D1 · Mi Comunidad
 * @var array $user @var array|null $membership @var array $latest @var array|null $event @var array|null $rune @var bool $canRunes */
$mes = mes_nombre((int) date('n'));
$featured = $latest[0] ?? null;
$minis = array_slice($latest, 1, 2);
$group = (string) config('site.social.whatsapp_group');
?>
<section class="member-head">
    <span class="wash wash--sea" aria-hidden="true"></span>
    <div class="container" style="position:relative">
        <span class="kicker">Mi Comunidad</span>
        <h1>Hola, <?= e(first_name($user['name'])) ?></h1>
        <span class="hand">esto es lo nuevo de <?= e($mes) ?></span>
    </div>
</section>

<div class="container dash">
    <div>
        <h2>Lo nuevo del mes</h2>
        <?php if (!$membership): ?>
            <div class="panel panel--sand" style="margin-bottom:18px">
                <h3>Todavía no tenés una membresía activa</h3>
                <p class="muted">Elegí Orilla o Inmersión para abrir las secciones de la Comunidad.</p>
                <a class="btn" href="/membresias">Ver membresías</a>
            </div>
        <?php endif; ?>
        <div class="dash__news">
            <?php if ($featured): ?>
                <article class="card card--feature" style="background:var(--blanco);border-color:var(--linea)">
                    <a class="card__media" href="<?= e($featured['url']) ?>" tabindex="-1" aria-hidden="true" style="min-height:280px"><?= $featured['image'] ? img($featured['image'], $featured['title']) : placeholder($featured['label'] ?? '') ?></a>
                    <div class="card__body">
                        <div style="display:flex;gap:10px;align-items:center"><span class="pill">Nueva</span><span class="kicker"><?= e($featured['section_name']) ?></span></div>
                        <h3><a href="<?= e($featured['url']) ?>"><?= e($featured['title']) ?></a></h3>
                        <p><?= e(excerpt($featured['excerpt'] ?: $featured['meta'], 90)) ?></p>
                        <div class="card__actions"><a class="btn" href="<?= e($featured['url']) ?>">Leer la entrega</a></div>
                    </div>
                </article>
            <?php endif; ?>
            <?php if ($minis): ?>
                <div class="dash__pair">
                    <?php foreach ($minis as $m): ?>
                        <article class="mini">
                            <?= $m['image'] ? img($m['image'], '') : '<span style="width:96px;height:96px;border-radius:12px;overflow:hidden;flex:none">' . placeholder('') . '</span>' ?>
                            <div>
                                <span class="kicker"><?= e($m['section_name']) ?></span>
                                <h3><?= e($m['title']) ?></h3>
                                <a class="link-arrow" href="<?= e($m['url']) ?>"><?= $m['media_url'] && str_ends_with(strtolower($m['media_url']), '.pdf') ? 'Descargar PDF' : 'Leer' ?> →</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <aside>
        <h2>Tu mes</h2>
        <div class="dash__side">
            <div class="row-card row-card--stack">
                <span class="icon-dot"><?= icon('users') ?></span>
                <div class="row-card__text"><h3>Próximo encuentro</h3><p><?= $event ? e(fecha($event['event_at'], true)) . ' · por Zoom' : 'Fecha y hora a confirmar · por Zoom' ?></p></div>
                <a class="btn btn--outline" href="<?= $event ? e('/contenidos/' . $event['section_slug'] . '/' . $event['slug'] . '/agendar') : '/contenidos/materiales-para-ver' ?>">Agendar</a>
            </div>
            <div class="row-card">
                <span class="icon-dot"><?= icon('mail') ?></span>
                <div class="row-card__text"><h3>Tu carta de <?= e($mes) ?></h3><p>En camino a tu casa</p></div>
            </div>
            <?php if ($canRunes): ?>
                <div class="row-card row-card--sky row-card--stack">
                    <span class="icon-dot"><?= icon('sparkle') ?></span>
                    <div class="row-card__text"><h3>Tu runa de hoy</h3><p><?= $rune ? e($rune['name'] . ' · ' . $rune['meaning']) : 'Sacá una del mazo' ?></p></div>
                    <a class="btn" href="/contenidos/runas-de-letras"><?= $rune ? 'Ver mi runa' : 'Sacar una runa' ?></a>
                </div>
            <?php endif; ?>
            <div class="row-card row-card--stack">
                <span class="icon-dot"><?= icon('cup') ?></span>
                <div class="row-card__text"><h3>Cafetería en tránsito</h3><p>Charlas en el grupo de WhatsApp</p></div>
                <a class="btn btn--outline" href="<?= e($group ?: '/contenidos/cafeteria-en-transito') ?>" <?= $group ? 'target="_blank" rel="noopener"' : '' ?>>Ir al grupo</a>
            </div>
        </div>
    </aside>
</div>

<section class="band band--white">
    <div class="container">
        <h2 style="font-size:1.85rem">Explorá por grupo</h2>
        <nav class="groups-pills" aria-label="Grupos">
            <a href="/contenidos#leer">Leer</a>
            <a href="/contenidos/materiales-para-escuchar">Ver y escuchar</a>
            <a href="/contenidos#crear">Crear</a>
            <a href="/contenidos#encontrarnos">Encuentros</a>
            <a href="/contenidos/lugares">Viajes y celebraciones</a>
        </nav>
    </div>
</section>
