<?php
/** Tarjeta de una sección (Contenidos, portada). @var array $section @var bool $open @var string|null $title */
$cfg = config('sections.' . $section['slug'], []);
$url = '/contenidos/' . $section['slug'];
?>
<article class="card section-card" data-group="<?= e($section['group_key']) ?>">
    <a class="card__media" href="<?= e($url) ?>" tabindex="-1" aria-hidden="true"><?= img($cfg['card_image'] ?? null, $section['name']) ?></a>
    <div class="card__body">
        <?php if (!empty($kicker)): ?>
            <span class="kicker"><?= e($kicker) ?></span>
        <?php else: ?>
            <span class="pill pill--soft card__chip"><?= e($cfg['chip'] ?? '') ?></span>
        <?php endif; ?>
        <h3><a href="<?= e($url) ?>"><?= e($title ?? $section['name']) ?></a></h3>
        <p><?= e($text ?? $cfg['card'] ?? $section['intro']) ?></p>
        <?php if ($open): ?>
            <span class="lock-line"><a href="<?= e($url) ?>">Ver la sección →</a></span>
        <?php else: ?>
            <span class="lock-line"><?= icon('lock') ?> <span>Para miembros · <a href="/membresias">Sumate</a> o <a href="/ingresar">Ingresá</a></span></span>
        <?php endif; ?>
    </div>
</article>
