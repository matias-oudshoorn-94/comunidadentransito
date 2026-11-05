<?php
/** A3 · Membresías: selector de planes, tabla de acceso por plan y preguntas */
$tiers = config('site.tiers');
?>
<section class="page-hero">
    <?= partial('partials/washes') ?>
    <div class="container container--narrow center">
        <span class="kicker">Membresías</span>
        <h1>Elegí cómo querés estar</h1>
        <p class="lead">Dos maneras de ser parte: Orilla para asomarte, Inmersión para vivirla completa.</p>
    </div>
</section>

<section class="section section--tight" style="padding-top:0">
    <div class="container">
        <?= partial('partials/plans', ['periods' => $periods]) ?>
    </div>
</section>

<section class="section section--white">
    <div class="container container--narrow">
        <div class="section__head center">
            <span class="kicker">Qué incluye cada plan</span>
            <h2>Acceso por sección</h2>
        </div>
        <div class="table-scroll">
            <table class="compare">
                <thead>
                    <tr>
                        <th scope="col">Sección</th>
                        <?php foreach ($tiers as $tier): ?><th scope="col"><?= e($tier['name']) ?></th><?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($groups as $key => $sections): ?>
                        <tr class="group-row"><td colspan="<?= count($tiers) + 1 ?>"><?= e(config("site.groups.$key")) ?></td></tr>
                        <?php foreach ($sections as $s): ?>
                            <tr>
                                <td><a href="/contenidos/<?= e($s['slug']) ?>"><?= e($s['name']) ?></a></td>
                                <?php foreach (array_keys($tiers) as $tier): ?>
                                    <td>
                                        <?php if (in_array($tier, $s['tiers'], true)): ?>
                                            <?= icon('check') ?><span class="visually-hidden">Incluida</span>
                                        <?php else: ?>
                                            <span class="off" aria-label="No incluida">—</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p class="center small muted" style="margin-top:20px">Pagás con Mercado Pago en pesos o con PayPal en dólares.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section__head center">
            <span class="kicker">Preguntas frecuentes</span>
            <h2>Antes de sumarte</h2>
        </div>
        <?= partial('partials/faq', ['faq' => $faq]) ?>
    </div>
</section>
