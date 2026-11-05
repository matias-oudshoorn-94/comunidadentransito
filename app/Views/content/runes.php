<?php /** B4 · Runas de Letras  @var array $section @var bool $hasAccess @var array|null $today @var bool $giftUsed */
$canDraw = $hasAccess || !$giftUsed;
?>
<section class="runes-page">
    <?= partial('partials/washes', ['animated' => true]) ?>
    <div class="container" style="position:relative;z-index:1">
        <span class="kicker"><?= e($section['kicker']) ?></span>
        <span class="hand"><?= e(config('sections.runas-de-letras.hand')) ?></span>
        <h1>Sacá una runa</h1>
        <p class="lead"><?= e($section['intro']) ?></p>

        <div class="runes__deck <?= $today ? 'is-done' : '' ?>" data-rune-deck data-url="/contenidos/runas-de-letras/sacar">
            <?php for ($i = 0; $i < 2; $i++): ?>
                <button class="rune-card" type="button" aria-label="Dar vuelta la carta <?= $i + 1 ?>" <?= $canDraw ? '' : 'disabled' ?>>
                    <span class="rune-card__back"><span><img src="<?= asset('img/logo-sintetico.webp') ?>" alt=""></span></span>
                </button>
            <?php endfor; ?>
        </div>

        <div class="rune-result <?= $today ? 'is-visible' : '' ?>" data-rune-result tabindex="-1" aria-live="polite">
            <div class="rune-face" data-rune-symbol><?= e($today['symbol'] ?? '') ?></div>
            <span class="kicker" data-rune-name><?= $today ? e($today['name'] . ' · ' . $today['meaning']) : '' ?></span>
            <blockquote data-rune-prompt><?= e($today['prompt'] ?? '') ?></blockquote>
            <p class="hand" style="font-size:1.4rem">tu runa de hoy</p>
            <p class="small muted" data-rune-after><?= $hasAccess ? 'Mañana te espera una nueva.' : 'Sumate para sacar una runa cada día.' ?></p>
            <?php if (!$hasAccess): ?><a class="btn" href="/membresias" style="margin-top:8px">Quiero sumarme</a><?php endif; ?>
        </div>

        <?php if (!$today): ?>
            <p class="runes__hint" data-rune-hint><?= $canDraw ? 'Hacé clic en una carta para darla vuelta' : 'Tu runa de regalo ya salió' ?></p>
            <?php if ($hasAccess): ?>
                <p class="small muted">Una runa nueva por día.</p>
            <?php elseif ($canDraw): ?>
                <a class="link-arrow" href="/membresias">Tu primera runa es de regalo</a>
            <?php else: ?>
                <a class="btn" href="/membresias">Sumate para sacar una cada día</a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>
