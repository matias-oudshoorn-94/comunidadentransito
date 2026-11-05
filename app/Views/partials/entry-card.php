<?php
/**
 * Tarjeta de una entrega.
 * Visitante: la de muestra lleva "Así lo ve un miembro" y se abre; las demás muestran "Para miembros · Sumate o Ingresá".
 * @var array $entry @var array $section @var bool $hasAccess
 */
$cfg = config('sections.' . $section['slug'], []);
$preview = (int) $entry['is_preview'] === 1;
$open = $hasAccess || $preview;
$url = "/contenidos/{$section['slug']}/{$entry['slug']}";
$label = $entry['label'] ?: ($cfg['label'] ?? '');
$book = ($cfg['cover'] ?? '') === 'book';
?>
<article class="card" data-group="<?= e($entry['category'] ?? '') ?>" data-year="<?= e(substr((string) $entry['published_at'], 0, 4)) ?>">
    <a class="card__media <?= $book ? 'card__media--book' : '' ?>" href="<?= e($url) ?>" tabindex="-1" aria-hidden="true">
        <?= $entry['image'] ? img($entry['image'], $entry['title']) : placeholder($label, !empty($cfg['play'])) ?>
        <?php if ($preview && !$hasAccess): ?><span class="pill pill--member">Así lo ve un miembro</span><?php endif; ?>
    </a>
    <div class="card__body">
        <?php if ($entry['kicker']): ?><span class="kicker"><?= e($entry['kicker']) ?></span><?php endif; ?>
        <h3><a href="<?= e($url) ?>"><?= e($entry['title']) ?></a></h3>
        <p><?= e(excerpt($entry['excerpt'] ?: $entry['meta'] ?: $entry['body'], 130)) ?></p>
        <?php if ($open): ?>
            <div class="card__actions">
                <a class="btn" href="<?= e($url) ?>"><?= e($cfg['action'] ?? 'Leer') ?></a>
                <?php if (!empty($cfg['second']) && ($preview || $hasAccess)): ?>
                    <a class="btn--link" href="<?= e(str_contains($cfg['second'], 'PDF') && $entry['media_url'] ? $entry['media_url'] : $url . (str_contains($cfg['second'], 'fotos') ? '#fotos' : '')) ?>"><?= e($cfg['second']) ?></a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <span class="lock-line"><?= icon('lock') ?> <span>Para miembros · <a href="/membresias">Sumate</a> o <a href="/ingresar">Ingresá</a></span></span>
        <?php endif; ?>
    </div>
</article>
