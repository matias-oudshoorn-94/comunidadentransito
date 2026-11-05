<?php
/**
 * Selector de planes: Mensual · Semestral · Anual.
 * @var array $periods [periodo => [tier => plan]]
 */
$tiers = config('site.tiers');
$periodLabels = ['mensual' => 'Mensual', 'semestral' => 'Semestral', 'anual' => 'Anual'];
$perLabels = ['mensual' => 'por mes', 'semestral' => 'cada 6 meses', 'anual' => 'por año'];
$discounts = ['semestral' => '−10%', 'anual' => '−20%'];
$features = [
    'orilla'    => ['Crear y escribir: Pispeando Arte, Tecniquitas y Runas de Letras', 'Materiales para leer, con PDF', 'Contenido nuevo cada semana'],
    'inmersion' => ['Todo lo de Orilla', 'Capituleando, Chirimbitas y grabaciones', 'Cafetería en tránsito: encuentros en vivo', 'Slow Mood, Lugares y Dejando Huellas'],
];

// Datos para que el JS cambie los precios sin recargar
$json = [];
foreach ($periods as $period => $byTier) {
    foreach ($byTier as $tier => $plan) {
        $only = !empty($plan['only_monthly']);
        $json[$period][$tier] = [
            'amount' => money($plan['price_ars']),
            'per'    => $perLabels[$only ? 'mensual' : $period],
            'usd'    => 'o ' . money($plan['price_usd'], 'USD') . ' con PayPal',
            'note'   => $only ? $tiers[$tier]['name'] . ' se paga mes a mes.' : ($plan['period'] === 'mensual' ? 'Cancelás cuando quieras.' : 'Un solo pago, sin renovación sorpresa.'),
            'url'    => '/compra?plan=' . $plan['slug'],
        ];
    }
}
$first = array_key_first($periods);
?>
<div class="plans-wrap center" data-plans>
    <script type="application/json"><?= json_encode($json, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?></script>

    <div class="plan-switch" role="tablist" aria-label="Período">
        <?php foreach ($periods as $period => $_): ?>
            <button type="button" role="tab" data-period="<?= e($period) ?>" aria-selected="<?= $period === $first ? 'true' : 'false' ?>">
                <?= e($periodLabels[$period]) ?><?php if (isset($discounts[$period])): ?><small><?= $discounts[$period] ?></small><?php endif; ?>
            </button>
        <?php endforeach; ?>
    </div>

    <div class="plans">
        <?php foreach ($periods[$first] as $tier => $plan): $data = $json[$first][$tier]; $featured = $tier === 'inmersion'; ?>
            <article class="plan <?= $featured ? 'plan--featured' : '' ?>" data-tier="<?= e($tier) ?>">
                <?php if ($featured): ?><span class="wash wash--light" aria-hidden="true"></span><?php endif; ?>
                <div class="card__meta">
                    <h3 class="plan__name"><?= e($tiers[$tier]['name']) ?></h3>
                    <?php if ($featured): ?><span class="chip chip--sea">La más elegida</span><?php endif; ?>
                </div>
                <p class="plan__blurb"><?= e($tiers[$tier]['blurb']) ?></p>
                <div class="plan__price" aria-live="polite">
                    <span class="plan__amount" data-amount><?= e($data['amount']) ?></span>
                    <span class="plan__per" data-per><?= e($data['per']) ?></span>
                </div>
                <div class="plan__usd" data-usd><?= e($data['usd']) ?></div>
                <ul class="plan__list">
                    <?php foreach ($features[$tier] as $f): ?>
                        <li><?= icon('check') ?><span><?= e($f) ?></span></li>
                    <?php endforeach; ?>
                </ul>
                <a class="btn <?= $featured ? '' : 'btn--ghost' ?> btn--block" href="<?= e($data['url']) ?>" data-cta>Elegir <?= e($tiers[$tier]['name']) ?></a>
                <p class="plan__note" data-note><?= e($data['note']) ?></p>
            </article>
        <?php endforeach; ?>
    </div>
</div>
