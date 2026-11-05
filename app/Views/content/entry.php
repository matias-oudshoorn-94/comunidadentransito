<?php
/** B5 · Vista de una entrega
 * @var array $section @var array $entry @var bool $canRead @var bool $hasAccess @var array $cfg
 * @var array|null $prev @var array|null $next @var array $more */
$slug = $section['slug'];
$kind = $entry['kind'];
$media = (string) $entry['media_url'];
$isPdf = $media !== '' && str_ends_with(strtolower(parse_url($media, PHP_URL_PATH) ?? ''), '.pdf');
$article = $slug === 'pispeando-arte';
[$body, $gallery] = rich_text($entry['body'], $canRead ? null : 2);
$sectionUrl = '/contenidos/' . $slug;

$embed = null;
if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/)([\w-]{6,})~', $media, $m)) {
    $embed = 'https://www.youtube-nocookie.com/embed/' . $m[1];
} elseif (preg_match('~vimeo\.com/(\d+)~', $media, $m)) {
    $embed = 'https://player.vimeo.com/video/' . $m[1];
}

$lock = function () use ($sectionUrl) { ?>
    <div class="lock-panel">
        <span class="icon-dot"><?= icon('lock') ?></span>
        <span class="hand" style="font-size:1.35rem">seguí leyendo</span>
        <h2>Esta entrega es para miembros de la Comunidad</h2>
        <p>Sumate para leerla completa, junto con todas las entregas anteriores.</p>
        <div class="actions">
            <a class="btn" href="/membresias">Sumate a la Comunidad</a>
            <a class="btn btn--outline" href="/ingresar">Ya soy miembro · Ingresar</a>
        </div>
    </div>
<?php };
?>

<?php if ($article): ?>
    <article class="entry">
        <div class="container container--narrow entry__head">
            <nav class="breadcrumb" aria-label="Ruta" style="margin-bottom:10px">
                <a href="/contenidos">Contenidos</a><span aria-hidden="true">›</span><a href="/contenidos#crear">Crear</a><span aria-hidden="true">›</span><a href="<?= e($sectionUrl) ?>"><?= e($section['name']) ?></a>
            </nav>
            <?php if ($entry['kicker']): ?><span class="kicker"><?= e($entry['kicker']) ?></span><?php endif; ?>
            <h1><?= e($entry['title']) ?></h1>
            <?php if ($entry['meta']): ?><p class="entry__meta"><?= e($entry['meta']) ?></p><?php endif; ?>
        </div>
        <?php if ($entry['image']): ?>
            <figure class="entry__wide container" style="padding:0;margin-bottom:24px">
                <?= img($entry['image'], $entry['image_caption'] ?: $entry['title'], '') ?>
                <?php if ($entry['image_caption']): ?><figcaption><?= e($entry['image_caption']) ?></figcaption><?php endif; ?>
            </figure>
        <?php endif; ?>
        <div class="container container--narrow">
            <div class="entry-body entry-body--sans"><?= $body ?></div>
            <?php if ($canRead): ?>
                <div class="signature-row"><img class="avatar avatar--sm" src="<?= asset('img/fotos/sil-sonrisa.jpg') ?>" alt="" style="object-fit:cover"><span class="signature">Con cariño, Silvina</span></div>
            <?php else: ?>
                <?php $lock(); ?>
            <?php endif; ?>
        </div>
    </article>

    <?php if ($canRead && ($prev || $next)): ?>
        <section class="band band--white band--tight">
            <div class="container prevnext">
                <?php if ($prev): ?><a href="<?= e($sectionUrl . '/' . $prev['slug']) ?>"><small>← Anterior</small><strong><?= e($prev['title']) ?></strong></a><?php else: ?><span></span><?php endif; ?>
                <?php if ($next): ?><a href="<?= e($sectionUrl . '/' . $next['slug']) ?>"><small>Siguiente →</small><strong><?= e($next['title']) ?></strong></a><?php endif; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($canRead && $more): ?>
        <section class="band">
            <div class="container">
                <div class="band__head"><span class="kicker">Seguí recorriendo</span><h2>Más para este mes</h2></div>
                <div class="grid grid--3" style="align-items:start">
                    <?php foreach ($more as $item): ?>
                        <article class="card">
                            <a class="card__media" href="<?= e($item['url']) ?>" tabindex="-1" aria-hidden="true" style="aspect-ratio:300/160"><?= img($item['image'], $item['title']) ?></a>
                            <div class="card__body">
                                <span class="kicker"><?= e($item['kicker']) ?></span>
                                <h3><a href="<?= e($item['url']) ?>"><?= e($item['title']) ?></a></h3>
                                <p><?= e($item['text']) ?></p>
                                <span class="lock-line"><a href="<?= e($item['url']) ?>"><?= e($item['link']) ?> →</a></span>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

<?php else: ?>
    <article class="entry">
        <div class="container container--<?= $kind === 'carta' ? 'narrow' : 'text' ?>">
            <div class="<?= $kind === 'carta' ? 'letter' : '' ?>">
                <div class="entry__head">
                    <a class="back-link" href="<?= e($sectionUrl) ?>">← Volver a <?= e($section['name']) ?></a>
                    <?php if ($entry['kicker']): ?><span class="kicker"><?= e($entry['kicker']) ?></span><?php endif; ?>
                    <h1><?= e($entry['title']) ?></h1>
                    <?php if ($entry['meta']): ?><p class="entry__meta"><?= e($entry['meta']) ?></p><?php endif; ?>
                    <?php if ($entry['hand_note']): ?><span class="hand entry__note"><?= e($entry['hand_note']) ?></span><?php endif; ?>
                </div>
                <hr class="entry__rule">

                <?php if ($canRead && $kind === 'episodio'): ?>
                    <div class="audio-box" data-audio>
                        <button class="audio-box__btn" type="button" aria-label="Reproducir" <?= $media ? '' : 'disabled' ?> data-audio-toggle><?= icon('triangle') ?></button>
                        <div class="audio-box__main">
                            <p class="audio-box__title"><?= e(ucfirst(lower(trim(preg_replace('/^PODCAST · /', '', (string) $entry['kicker']))))) ?></p>
                            <div class="audio-box__bar"><span data-audio-bar></span></div>
                            <div class="audio-box__time" data-audio-time>0:00 · <?= $media ? 'escuchá cuando quieras' : 'duración a confirmar' ?></div>
                        </div>
                        <?php if ($media): ?><audio preload="none" src="<?= e($media) ?>"></audio><?php endif; ?>
                    </div>
                <?php elseif ($canRead && $kind === 'grabacion'): ?>
                    <div class="video-box">
                        <?php if ($embed): ?>
                            <iframe src="<?= e($embed) ?>" title="<?= e($entry['title']) ?>" allow="fullscreen; picture-in-picture" loading="lazy"></iframe>
                        <?php elseif ($media): ?>
                            <video controls preload="none" src="<?= e($media) ?>"></video>
                        <?php else: ?>
                            <?= placeholder('') ?>
                            <span class="play play--solid" style="position:absolute;left:50%;top:50%;transform:translate(-50%,-50%)"><?= icon('triangle') ?></span>
                            <div class="video-box__bar">0:00 / grabación a confirmar<span></span></div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="entry-body"><?= $body ?></div>

                <?php if ($canRead && in_array($kind, ['carta', 'relato'], true) && $slug !== 'dejando-huellas'): ?>
                    <p class="signature">— Silvina</p>
                <?php endif; ?>

                <?php if ($canRead): ?>
                    <hr class="entry__rule">
                    <div class="entry__actions">
                        <?php if ($isPdf): ?><a class="btn" href="<?= e($media) ?>" download>Descargar PDF</a><?php endif; ?>
                        <?php if (!empty($cfg['detail_button'])): ?><a class="btn" href="<?= e($sectionUrl) ?>"><?= e($cfg['detail_button']) ?></a><?php endif; ?>
                        <a class="link-arrow" href="<?= e($sectionUrl) ?>"><?= e($cfg['more'] ?? 'Ver más') ?> →</a>
                    </div>
                <?php else: ?>
                    <?php $lock(); ?>
                <?php endif; ?>
            </div>
        </div>
    </article>

    <?php if ($canRead && $gallery): ?>
        <section class="gallery" id="fotos">
            <span class="hand"><?= e($gallery['title']) ?></span>
            <div class="strip">
                <div class="strip__track">
                    <?php foreach ([false, true] as $copy): foreach ($gallery['images'] as $src): ?>
                        <div class="strip__item" <?= $copy ? 'aria-hidden="true"' : '' ?>><?= img($src, '', 'loading="lazy"') ?></div>
                    <?php endforeach; endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>
<?php endif; ?>
