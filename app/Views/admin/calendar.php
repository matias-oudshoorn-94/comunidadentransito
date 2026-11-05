<?php /** @var string $month @var string $label @var string $prev @var string $next @var array $weeks @var array $events */ ?>
<section class="box">
    <div class="cal-head" style="margin-bottom:18px">
        <h2><?= e($label) ?></h2>
        <a class="btn btn--ghost btn--sm" href="?mes=<?= e($prev) ?>" aria-label="Mes anterior">←</a>
        <a class="btn btn--soft btn--sm" href="?mes=<?= date('Y-m') ?>">Hoy</a>
        <a class="btn btn--ghost btn--sm" href="?mes=<?= e($next) ?>" aria-label="Mes siguiente">→</a>
    </div>
    <div class="cal-legend" style="margin-bottom:14px">
        <span><i style="background:var(--alerta)"></i> Vence una membresía</span>
        <span><i style="background:var(--turquesa)"></i> Empieza una membresía</span>
        <span><i style="background:var(--arena)"></i> Pago aprobado</span>
    </div>
    <div class="cal">
        <?php foreach (['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'] as $d): ?><div class="cal__dow"><?= $d ?></div><?php endforeach; ?>
        <?php foreach ($weeks as $week): foreach ($week as $day): ?>
            <?php if ($day === null): ?>
                <div class="cal__day cal__day--empty"></div>
            <?php else: $list = $events[$day] ?? []; ?>
                <div class="cal__day <?= $day === date('Y-m-d') ? 'cal__day--today' : '' ?>">
                    <span class="cal__num"><?= (int) substr($day, 8) ?><span class="visually-hidden"> de <?= e($label) ?></span></span>
                    <?php foreach (array_slice($list, 0, 4) as $ev): ?>
                        <a class="ev ev--<?= e($ev['type']) ?>" href="<?= e($ev['url']) ?>" title="<?= e(ucfirst($ev['type']) . ': ' . $ev['label'] . ' · ' . $ev['meta']) ?>">
                            <?= e($ev['label']) ?> · <?= e($ev['meta']) ?>
                        </a>
                    <?php endforeach; ?>
                    <?php if (count($list) > 4): ?>
                        <details><summary class="ev-more">+<?= count($list) - 4 ?> más</summary>
                            <?php foreach (array_slice($list, 4) as $ev): ?><a class="ev ev--<?= e($ev['type']) ?>" href="<?= e($ev['url']) ?>"><?= e($ev['label']) ?></a><?php endforeach; ?>
                        </details>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endforeach; endforeach; ?>
    </div>
</section>
