<?php
/**
 * Encabezado de página con foto a la derecha.
 * @var string $title  @var string|null $kicker @var string|null $hand @var string|null $text
 * @var array|null $crumbs [[texto, url|null]] @var string|null $image @var string|null $frame @var string|null $caption
 * @var array|null $facts [[icono|null, título, detalle]] @var string|null $actions (html) @var bool|null $animated
 */
?>
<section class="hero">
    <?= partial('partials/washes', ['animated' => $animated ?? false]) ?>
    <div class="container hero__grid">
        <div>
            <?php if (!empty($crumbs)): ?>
                <nav class="breadcrumb" aria-label="Ruta">
                    <?php foreach ($crumbs as $i => [$label, $url]): ?>
                        <?= $i ? '<span aria-hidden="true">›</span>' : '' ?>
                        <?= $url ? '<a href="' . e($url) . '">' . e($label) . '</a>' : '<span>' . e($label) . '</span>' ?>
                    <?php endforeach; ?>
                </nav>
            <?php endif; ?>
            <?php if (!empty($kicker)): ?><span class="kicker"><?= e($kicker) ?></span><?php endif; ?>
            <?php if (!empty($hand)): ?><span class="hand"><?= e($hand) ?></span><?php endif; ?>
            <h1><?= e($title) ?></h1>
            <?php if (!empty($text)): ?><p class="lead"><?= e($text) ?></p><?php endif; ?>
            <?php if (!empty($facts)): ?>
                <div class="facts">
                    <?php foreach ($facts as [$ic, $t, $d]): ?>
                        <div class="fact"><?php if ($ic): ?><span class="icon-dot"><?= icon($ic) ?></span><?php endif; ?><div><strong><?= e($t) ?></strong><span><?= e($d) ?></span></div></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($actions)): ?><div class="actions"><?= $actions ?></div><?php endif; ?>
        </div>
        <?php if (!empty($image) || !empty($images)): ?>
            <div class="hero__photo photo-halo <?= !empty($images) ? 'hero__photo--stack' : '' ?>">
                <?php foreach ($images ?? [$image] as $src): ?>
                    <div class="photo <?= ($frame ?? '') === 'polaroid' ? 'photo--polaroid' : '' ?>" <?= !empty($ratio) ? 'style="aspect-ratio:' . e($ratio) . '"' : '' ?>><?= img($src, $title, '') ?></div>
                <?php endforeach; ?>
                <?php if (!empty($caption)): ?><p class="photo-caption"><?= e($caption) ?></p><?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
