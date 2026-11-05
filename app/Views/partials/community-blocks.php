<?php
/** Bloques compartidos entre la portada y La Comunidad. @var string $checksBg (sky|sand) */
$values = [
    ['heart', 'Con sentido', 'Cada propuesta está elegida por lo que genera: calma, foco, alegría, encuentro.'],
    ['pen', 'Con las manos', 'Pintar, escribir, crear. Acá las cosas se hacen, no solo se miran.'],
    ['users', 'Con otros y sin apuro', 'Compartir con personas que buscan lo mismo, en encuentros y charlas, cada quien a su tiempo.'],
];
$checks = [
    'Te gusta crear, pero te cuesta hacerte el tiempo.', 'Querés escribir, pintar o leer sin presión.',
    'Disfrutás de las cartas, los viajes y las pequeñas cosas.', 'Buscás un grupo con tus mismos intereses.',
];
$benefits = [
    ['mail', 'Slow Mood', 'Una carta escrita por Silvina que te llega por correo.'],
    ['users', 'Webinars', 'Una charla por Zoom, que queda grabada.'],
    ['cup', 'Cafetería en tránsito', 'Charlas e intercambio en el grupo de WhatsApp.'],
    ['pen', 'Contenidos para vos', 'Propuestas para leer, ver, escuchar y crear.'],
    ['book', 'Capítulo de regalo', 'Un capítulo de sus libros, cada mes.'],
    ['tag', '25% en libros', 'Descuento en sus libros en papel.'],
];
?>
<section class="band band--white">
    <div class="container">
        <div class="band__head center">
            <span class="kicker">Te damos la bienvenida a Comunidad en Tránsito</span>
            <h2>No son hobbies sueltos: es un hacer pensado para vos</h2>
            <p class="lead">Un lugar para ser parte y transitar, a tu ritmo, por propuestas de arte, escritura, viajes, celebraciones, espiritualidad y bienestar. Palabras, obras, textos y audios para acompañar tu crecimiento personal y darle lugar a lo que es significativo para vos. Animate a vivir esta experiencia junto a otras personas.</p>
        </div>
        <div class="grid grid--3" style="align-items:start">
            <?php foreach ($values as [$ic, $t, $d]): ?>
                <div class="tile"><span class="icon-dot"><?= icon($ic) ?></span><h3><?= e($t) ?></h3><p><?= e($d) ?></p></div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="band band--<?= e($checksBg ?? 'sky') ?>">
    <div class="container">
        <span class="kicker">Es para vos si…</span>
        <ul class="checks" style="margin-top:18px">
            <?php foreach ($checks as $c): ?><li><span class="icon-dot"><?= icon('check') ?></span><?= e($c) ?></li><?php endforeach; ?>
        </ul>
        <div class="recognize"><h2>¿Te reconocés?</h2><span class="hand">si dijiste que sí a alguna, este lugar es para vos</span></div>
    </div>
</section>

<section class="band band--white">
    <div class="container">
        <div class="band__head"><span class="kicker">Qué recibís</span><h2>Cada mes, en la Comunidad</h2></div>
        <div class="benefits">
            <?php foreach ($benefits as [$ic, $t, $d]): ?>
                <div class="benefit"><span class="icon-dot"><?= icon($ic) ?></span><div><h3><?= e($t) ?></h3><p><?= e($d) ?></p></div></div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
