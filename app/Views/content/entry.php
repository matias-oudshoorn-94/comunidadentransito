<?php
/** B5 · Vista de una entrega: lectura, episodio, grabación, carta, relato o encuentro
 * @var array $section @var array $entry @var bool $hasAccess @var array $more */
use App\Models\Entry;

$kind = $entry['kind'];
$media = (string) $entry['media_url'];
$isPdf = $media !== '' && str_ends_with(strtolower(parse_url($media, PHP_URL_PATH) ?? ''), '.pdf');

// YouTube / Vimeo → embed
$embed = null;
if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/)([\w-]{6,})~', $media, $m)) {
    $embed = 'https://www.youtube-nocookie.com/embed/' . $m[1];
} elseif (preg_match('~vimeo\.com/(\d+)~', $media, $m)) {
    $embed = 'https://player.vimeo.com/video/' . $m[1];
}
?>
<section class="page-hero">
    <?= partial('partials/washes', ['animated' => (int) $section['animated_header'] === 1]) ?>
    <div class="container entry-head">
        <nav class="breadcrumb" aria-label="Ruta">
            <a href="/contenidos">Contenidos</a> <span aria-hidden="true">/</span>
            <a href="/contenidos/<?= e($section['slug']) ?>"><?= e($section['name']) ?></a>
        </nav>
        <?php if (!$hasAccess): ?><span class="chip chip--member" style="margin-bottom:16px">Así lo ve un miembro</span><br><?php endif; ?>
        <span class="kicker"><?= e(Entry::KINDS[$kind] ?? 'Entrega') ?></span>
        <h1><?= e($entry['title']) ?></h1>
        <div class="entry-meta">
            <span><?= icon('calendar') ?></span><span><?= fecha($entry['published_at']) ?></span>
        </div>
    </div>
</section>

<article class="section section--tight" style="padding-top:0">
    <div class="container container--text">

        <?php if ($kind === 'episodio'): ?>
            <div class="media-box">
                <?php if ($media): ?>
                    <audio controls preload="none" src="<?= e($media) ?>"></audio>
                <?php else: ?>
                    <div class="media-box__empty" style="aspect-ratio:auto"><?= icon('headphones') ?><span>El audio de este episodio se sube desde el admin.</span></div>
                <?php endif; ?>
            </div>
        <?php elseif ($kind === 'grabacion'): ?>
            <div class="media-box">
                <div class="media-box__video">
                    <?php if ($embed): ?>
                        <iframe src="<?= e($embed) ?>" title="<?= e($entry['title']) ?>" allow="fullscreen; picture-in-picture" loading="lazy"></iframe>
                    <?php elseif ($media): ?>
                        <video controls preload="none" src="<?= e($media) ?>"></video>
                    <?php else: ?>
                        <div class="media-box__empty" style="background:var(--celeste-claro)"><?= icon('play') ?><span>La grabación se sube desde el admin.</span></div>
                    <?php endif; ?>
                </div>
            </div>
        <?php elseif ($kind === 'encuentro' && $entry['event_at']): $ts = strtotime($entry['event_at']); ?>
            <div class="media-box event-box">
                <div class="event-box__date">
                    <div class="event-box__day"><span><?= e(substr(mes_nombre((int) date('n', $ts)), 0, 3)) ?></span><strong><?= date('j', $ts) ?></strong></div>
                    <div><strong><?= e(ucfirst(DIAS[(int) date('w', $ts)])) ?>, <?= date('H:i', $ts) ?> h</strong><br><span class="muted small">Hora de Argentina · por Zoom</span></div>
                </div>
                <a class="btn btn--sm" href="/contenidos/<?= e($section['slug']) ?>/<?= e($entry['slug']) ?>/agendar"><?= icon('calendar') ?> Agendar</a>
            </div>
        <?php endif; ?>

        <?php if ($kind === 'relato' && $section['slug'] === 'dejando-huellas'): ?>
            <div class="strip" style="margin-bottom:36px">
                <div class="strip__track">
                    <?php foreach ([false, true] as $copy): foreach (config('site.strip_photos') as $p): ?>
                        <div class="strip__item" <?= $copy ? 'aria-hidden="true"' : '' ?>><?= photo($p['src'], $p['alt']) ?></div>
                    <?php endforeach; endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($kind === 'carta'): ?>
            <div class="entry-letter">
                <div class="entry-body"><?= paragraphs($entry['body']) ?></div>
                <div class="signature">Sil</div>
            </div>
        <?php else: ?>
            <div class="entry-body"><?= paragraphs($entry['body']) ?></div>
        <?php endif; ?>

        <?php if ($isPdf): ?>
            <div class="actions" style="margin-top:32px">
                <a class="btn" href="<?= e($media) ?>" download><?= icon('download') ?> Descargar PDF</a>
            </div>
        <?php endif; ?>

        <?php if (!$hasAccess): ?>
            <div class="locked-more">
                <span class="kicker">¿Te gustó?</span>
                <h2 style="font-size:1.7rem">Hay mucho más del otro lado</h2>
                <p class="muted">Con una membresía abrís todas las entregas de <?= e($section['name']) ?>.</p>
                <a class="btn" href="/membresias">Ver membresías</a>
            </div>
        <?php endif; ?>
    </div>
</article>

<?php if ($more): ?>
    <section class="section section--white section--tight">
        <div class="container">
            <div class="group-block__head">
                <h2>Más de <?= e($section['name']) ?></h2>
                <a href="/contenidos/<?= e($section['slug']) ?>">Ver la sección →</a>
            </div>
            <div class="grid grid--3">
                <?php foreach ($more as $item): ?>
                    <?= partial('partials/entry-card', ['entry' => $item, 'section' => $section, 'hasAccess' => $hasAccess]) ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>
