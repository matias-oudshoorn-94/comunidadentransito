<?php /** A2 · La Comunidad  @var string $fromPrice */ ?>
<?= partial('partials/hero', [
    'kicker' => 'La Comunidad', 'hand' => 'un lugar para vos, todos los meses',
    'title' => 'Un lugar para crear, escribir y encontrarte',
    'text' => 'Comunidad en Tránsito es un espacio guiado por Silvina Oudshoorn para darte un rato para vos cada mes, sin presión y sin necesidad de saber de arte.',
    'actions' => '<a class="btn" href="/membresias">Ver membresías</a>',
    'images' => ['fotos/cafe-taza.jpg', 'fotos/sil-cafe-flores.jpg'],
]) ?>

<?= partial('partials/community-blocks', ['checksBg' => 'sand']) ?>

<section class="band">
    <div class="container">
        <div class="band__head"><span class="kicker">Un mes en la Comunidad</span><h2>Así se vive un mes</h2><span class="hand" style="font-size:1.35rem">con un cafecito de por medio</span></div>
        <div class="steps">
            <div class="step"><span class="step__n">1</span><h3>Nuevas entregas</h3><p>Arte, lugares y escritura.</p></div>
            <div class="step"><span class="step__n">2</span><h3>Encuentro del mes</h3><p>Por Zoom. Queda grabado.</p></div>
            <div class="step"><span class="step__n">3</span><h3>Carta Slow Mood</h3><p>Te llega por correo, escrita por Silvina.</p></div>
            <div class="step step--sky"><span class="step__n">∞</span><h3>Cafetería en tránsito</h3><p>Charlas todo el mes en el grupo de WhatsApp.</p></div>
        </div>
    </div>
</section>

<?= partial('partials/testimonials') ?>

<?= partial('partials/cta', ['hand' => '¿te animás?', 'title' => 'Elegí cómo sumarte', 'button' => 'Ver membresías', 'bg' => 'cream',
    'extra' => '<p style="margin:0">Desde ' . e($fromPrice) . ' por mes · Cancelás cuando quieras</p>']) ?>
