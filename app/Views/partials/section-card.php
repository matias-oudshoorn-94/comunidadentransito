<?php
/** @var array $section  @var bool|null $unlocked */
$tierNames = array_map(fn ($t) => config("site.tiers.$t.name"), $section['tiers']);
?>
<a class="card section-card" href="/contenidos/<?= e($section['slug']) ?>" data-group="<?= e($section['group_key']) ?>">
    <div class="card__meta">
        <span class="card__icon"><?= icon($section['icon'] ?? 'sparkle') ?></span>
        <?php if (isset($unlocked)): ?>
            <span class="chip <?= $unlocked ? '' : 'chip--sand' ?>"><?= icon($unlocked ? 'unlock' : 'lock') ?><?= $unlocked ? 'Abierta' : e(implode(' · ', $tierNames)) ?></span>
        <?php endif; ?>
    </div>
    <span class="kicker" style="margin:0"><?= e($section['kicker']) ?></span>
    <h3><?= e($section['name']) ?></h3>
    <p><?= e($section['intro']) ?></p>
    <span class="card__foot">Ver la sección <?= icon('arrow') ?></span>
</a>
