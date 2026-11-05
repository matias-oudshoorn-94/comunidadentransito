<?php /** B4 · Runas de Letras  @var array $section @var bool $hasAccess @var array|null $today */ ?>
<section class="page-hero">
    <?= partial('partials/washes', ['animated' => true]) ?>
    <div class="container container--narrow center">
        <nav class="breadcrumb" aria-label="Ruta" style="justify-content:center">
            <a href="/contenidos">Contenidos para vos</a> <span aria-hidden="true">/</span> <span>Crear y escribir</span>
        </nav>
        <span class="kicker"><?= e($section['kicker']) ?></span>
        <h1>Runas de Letras</h1>
        <p class="lead"><?= e($section['intro']) ?></p>
    </div>
</section>

<section class="section section--tight runes" style="padding-top:0">
    <div class="container">
        <?php if (!$hasAccess): ?>
            <div class="runes__deck" aria-hidden="true">
                <?php for ($i = 0; $i < 5; $i++): ?>
                    <span class="rune-card" style="cursor:default"><span class="rune-card__back"></span></span>
                <?php endfor; ?>
            </div>
            <div class="locked-more" style="max-width:640px;margin-inline:auto">
                <span class="chip chip--member" style="margin-bottom:14px">Así lo ve un miembro</span>
                <h2 style="font-size:1.6rem">Cada día, una runa nueva</h2>
                <p class="muted">Elegís una carta, se da vuelta y aparece tu runa con una consigna para escribir. Mañana, otra.</p>
                <a class="btn" href="/membresias">Ver membresías</a>
                <?php if (!auth()): ?><p class="small" style="margin:14px 0 0">¿Ya sos parte? <a href="/ingresar">Ingresá</a></p><?php endif; ?>
            </div>
        <?php else: ?>
            <p class="center muted" <?= $today ? 'hidden' : '' ?>>Respirá, elegí una carta y dejate sorprender.</p>

            <div class="runes__deck <?= $today ? 'is-done' : '' ?>" data-rune-deck data-url="/contenidos/runas-de-letras/sacar">
                <?php for ($i = 0; $i < 5; $i++): ?>
                    <button class="rune-card" type="button" aria-label="Elegir la carta <?= $i + 1 ?>"><span class="rune-card__back"></span></button>
                <?php endfor; ?>
            </div>

            <div class="rune-result <?= $today ? 'is-visible' : '' ?>" data-rune-result tabindex="-1" aria-live="polite">
                <div class="rune-face" data-rune-symbol><?= e($today['symbol'] ?? '') ?></div>
                <span class="kicker" data-rune-name><?= $today ? e($today['name'] . ' · ' . $today['meaning']) : '' ?></span>
                <blockquote data-rune-prompt><?= e($today['prompt'] ?? '') ?></blockquote>
                <p class="hand">tu runa de hoy</p>
                <p class="small muted">Mañana te espera una nueva.</p>
            </div>
        <?php endif; ?>
    </div>
</section>
