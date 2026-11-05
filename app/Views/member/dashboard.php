<?php
/** D1 · Mi Comunidad
 * @var array $user @var array|null $membership @var array $sections @var array $latest @var array $events @var array|null $rune @var bool $canRunes */
$firstName = explode(' ', trim($user['name']))[0];
?>
<section class="member-hero has-washes">
    <?= partial('partials/washes') ?>
    <div class="container">
        <span class="kicker">Mi Comunidad</span>
        <h1>Hola, <?= e($firstName) ?></h1>
        <p class="lead"><?= $membership ? 'Esto es lo nuevo desde tu última visita.' : 'Todavía no tenés una membresía activa.' ?></p>
    </div>
</section>

<section class="section section--tight" style="padding-top:0">
    <div class="container member-grid">
        <div class="grid" style="gap:28px">
            <?php if (!$membership): ?>
                <div class="panel panel--sand">
                    <h2 style="font-size:1.5rem">Elegí tu membresía</h2>
                    <p class="muted">Con Orilla o Inmersión se abren las secciones de la comunidad.</p>
                    <a class="btn" href="/membresias">Ver membresías</a>
                </div>
            <?php endif; ?>

            <?php if ($sections): ?>
                <div class="panel">
                    <h2 style="font-size:1.4rem">Tus secciones</h2>
                    <div class="shortcut-grid">
                        <?php foreach ($sections as $s): ?>
                            <a class="shortcut" href="/contenidos/<?= e($s['slug']) ?>"><?= icon($s['icon'] ?? 'sparkle') ?><?= e($s['name']) ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($latest): ?>
                <div class="panel">
                    <h2 style="font-size:1.4rem">Lo último</h2>
                    <ul class="list-plain">
                        <?php foreach ($latest as $item): ?>
                            <li>
                                <a class="list-row" href="/contenidos/<?= e($item['section_slug']) ?>/<?= e($item['slug']) ?>">
                                    <div><span class="kicker" style="margin:0 0 4px;font-size:.66rem"><?= e($item['section_name']) ?></span><h4><?= e($item['title']) ?></h4></div>
                                    <span class="small muted"><?= fecha_corta($item['published_at']) ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>

        <aside class="grid" style="gap:28px">
            <?php if ($membership): ?>
                <div class="panel panel--sky member-card">
                    <span class="kicker" style="margin:0">Tu membresía</span>
                    <span class="member-card__plan"><?= e($membership['plan_name']) ?></span>
                    <span class="status status--active">Activa hasta el <?= fecha($membership['ends_at']) ?></span>
                    <a href="/mi-cuenta" class="small" style="margin-top:8px">Ver Mi cuenta →</a>
                </div>
            <?php endif; ?>

            <?php if ($canRunes): ?>
                <div class="panel center">
                    <span class="kicker">Runa del día</span>
                    <?php if ($rune): ?>
                        <div class="rune-face" style="width:96px;font-size:2.6rem;margin-bottom:14px"><?= e($rune['symbol']) ?></div>
                        <p style="font-family:var(--font-title);font-weight:600"><?= e($rune['prompt']) ?></p>
                    <?php else: ?>
                        <p class="muted">Todavía no sacaste tu runa de hoy.</p>
                        <a class="btn btn--soft btn--sm" href="/contenidos/runas-de-letras">Sacar una runa</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($events): ?>
                <div class="panel">
                    <h2 style="font-size:1.3rem">Próximos encuentros</h2>
                    <ul class="list-plain">
                        <?php foreach ($events as $ev): ?>
                            <li><a class="list-row" href="/contenidos/<?= e($ev['section_slug']) ?>/<?= e($ev['slug']) ?>"><div><h4><?= e($ev['title']) ?></h4><span class="small muted"><?= fecha($ev['event_at'], true) ?></span></div><?= icon('calendar') ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </aside>
    </div>
</section>
