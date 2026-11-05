<?php
/**
 * Tarjeta de entrega.
 * Sin acceso: la de muestra lleva "Así lo ve un miembro" y abre el detalle; las demás muestran el candado y van a Membresías.
 * @var array $entry  @var array $section  @var bool $hasAccess
 */
use App\Models\Entry;

$open = $hasAccess || (int) $entry['is_preview'] === 1;
$href = $open ? "/contenidos/{$section['slug']}/{$entry['slug']}" : '/membresias';
$kind = Entry::KINDS[$entry['kind']] ?? 'Entrega';
?>
<a class="card <?= $open ? '' : 'card--locked' ?>" href="<?= e($href) ?>">
    <div class="card__media">
        <?= photo('contenidos/' . $entry['slug'] . '.jpg', $entry['title']) ?>
        <?php if (!$hasAccess && (int) $entry['is_preview'] === 1): ?>
            <span class="chip chip--member member-label">Así lo ve un miembro</span>
        <?php endif; ?>
        <?php if (!$open): ?>
            <span class="lock-badge" aria-label="Solo para miembros"><?= icon('lock') ?></span>
        <?php endif; ?>
    </div>
    <div class="card__body">
        <div class="card__meta">
            <span class="kicker" style="margin:0"><?= e($kind) ?></span>
            <span class="small muted"><?= e($entry['event_at'] ? fecha($entry['event_at']) : fecha($entry['published_at'])) ?></span>
        </div>
        <h3><?= e($entry['title']) ?></h3>
        <p><?= e(excerpt($entry['excerpt'] ?: $entry['body'], 120)) ?></p>
        <span class="card__foot">
            <?php if ($open): ?>
                <?= match ($entry['kind']) { 'episodio' => 'Escuchar', 'grabacion' => 'Ver la grabación', 'encuentro' => 'Ver el encuentro', 'carta' => 'Leer la carta', default => 'Leer' } ?> <?= icon('arrow') ?>
            <?php else: ?>
                <?= icon('lock') ?> Solo para miembros
            <?php endif; ?>
        </span>
    </div>
</a>
