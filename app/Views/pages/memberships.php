<?php /** A3 · Membresías  @var array $periods */
$rows = [
    ['Carta Slow Mood', true, true], ['Webinars', true, true], ['Cafetería en tránsito', true, true],
    ['Contenidos para vos', 'Algunos', 'Todos'], ['Capítulo de regalo', false, true], ['25% en libros', false, true],
];
?>
<section class="hero hero--center">
    <?= partial('partials/washes') ?>
    <div class="container">
        <span class="kicker">Membresías</span>
        <span class="hand">a tu ritmo, sin apuro</span>
        <h1>Elegí cómo sumarte</h1>
        <p class="lead" style="margin-bottom:28px">Dos formas de ser parte. Podés cambiar de plan o cancelar cuando quieras.</p>
        <?= partial('partials/plans', ['periods' => $periods, 'full' => true]) ?>
        <p class="plans-note"><?= icon('card') ?> Pagás con Mercado Pago · Cancelás cuando quieras desde Mi cuenta</p>
    </div>
</section>

<section class="band band--white">
    <div class="container">
        <div class="band__head center"><span class="kicker">Comparar</span><h2>Qué incluye cada plan</h2></div>
        <div class="compare-box">
            <table class="compare">
                <thead><tr><th scope="col"><span class="visually-hidden">Beneficio</span></th><th scope="col">Orilla</th><th scope="col">Inmersión</th></tr></thead>
                <tbody>
                    <?php foreach ($rows as [$label, $a, $b]): ?>
                        <tr>
                            <td><?= e($label) ?></td>
                            <?php foreach ([$a, $b] as $v): ?>
                                <td><?= $v === true ? icon('check') . '<span class="visually-hidden">Incluido</span>' : ($v === false ? '<span aria-label="No incluido">✕</span>' : e($v)) ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<section class="band" id="preguntas">
    <div class="container faq-split">
        <div><span class="kicker">Dudas sobre el pago</span><h2>Antes de sumarte</h2><span class="hand" style="font-size:1.3rem">si te queda alguna duda, escribime</span></div>
        <div>
            <?= partial('partials/faq', ['faq' => config('site.faq_payments'), 'open' => 0]) ?>
            <a class="link-arrow" href="<?= e(whatsapp()) ?>" target="_blank" rel="noopener" style="display:inline-flex;gap:8px;margin-top:20px"><?= icon('whatsapp', 'icon') ?> ¿Otra duda? Escribime por WhatsApp</a>
        </div>
    </div>
</section>

<?= partial('partials/cta', ['hand' => '¿te animás?', 'title' => 'Tu lugar te está esperando', 'button' => 'Sumate a la Comunidad', 'href' => '/compra?plan=inmersion-mensual', 'bg' => 'sky']) ?>
