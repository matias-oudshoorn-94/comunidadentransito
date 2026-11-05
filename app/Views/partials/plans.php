<?php
/**
 * Selector Mensual · Semestral · Anual y las dos tarjetas (Orilla, Inmersión).
 * @var array $periods [periodo => [tier => plan]] @var bool|null $full (Membresías muestra todos los beneficios)
 */
$tiers = config('site.tiers');
$labels = ['mensual' => 'Mensual', 'semestral' => 'Semestral', 'anual' => 'Anual · 2 meses de regalo'];
$per = ['mensual' => 'por mes', 'semestral' => 'por semestre', 'anual' => 'por año'];
$renew = ['mensual' => 'Se renueva cada mes', 'semestral' => 'Se renueva cada 6 meses', 'anual' => 'Se renueva cada año'];
$json = [];
foreach ($periods as $period => $byTier) {
    foreach ($byTier as $tier => $plan) {
        $p = !empty($plan['only_monthly']) ? 'mensual' : $period;
        $json[$period][$tier] = ['amount' => money($plan['price_ars']), 'per' => $per[$p], 'renew' => $renew[$p], 'url' => '/compra?plan=' . $plan['slug']];
    }
}
$first = array_key_first($periods);
?>
<div class="center" data-plans>
    <script type="application/json"><?= json_encode($json, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?></script>
    <div class="plan-switch" role="tablist" aria-label="Período">
        <?php foreach ($periods as $period => $_): ?>
            <button type="button" role="tab" data-period="<?= e($period) ?>" aria-selected="<?= $period === $first ? 'true' : 'false' ?>"><?= e($labels[$period]) ?></button>
        <?php endforeach; ?>
    </div>
    <div class="plans">
        <?php foreach (['orilla', 'inmersion'] as $tier): if (!isset($json[$first][$tier])) continue; $d = $json[$first][$tier]; $featured = $tier === 'inmersion';
            $features = $tiers[$tier]['features'];
            if (empty($full)) { $features = array_values(array_filter($features, fn ($f) => !str_contains($f, '(a definir)'))); } ?>
            <article class="plan <?= $featured ? 'plan--featured' : '' ?>" data-tier="<?= e($tier) ?>">
                <?php if ($featured): ?><span class="pill pill--hand">La que recomiendo</span><?php endif; ?>
                <h3 class="plan__name"><?= e($tiers[$tier]['name']) ?></h3>
                <div class="plan__price" aria-live="polite">
                    <span class="plan__amount" data-amount><?= e($d['amount']) ?></span>
                    <span class="plan__per" data-per><?= e($d['per']) ?></span>
                </div>
                <span class="plan__renew" data-renew><?= e($d['renew']) ?></span>
                <p class="plan__blurb"><?= e($tiers[$tier]['blurb']) ?></p>
                <ul class="plan__list">
                    <?php foreach ($features as $f): ?><li><?= icon('check') ?><?= e($f) ?></li><?php endforeach; ?>
                </ul>
                <a class="btn btn--block <?= $featured ? '' : 'btn--outline' ?>" href="<?= e($d['url']) ?>" data-cta>Elegir <?= e($tiers[$tier]['name']) ?></a>
            </article>
        <?php endforeach; ?>
    </div>
</div>
