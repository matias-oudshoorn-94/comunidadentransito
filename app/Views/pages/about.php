<?php /** A5 · Sobre Silvina */
$video = (string) config('site.about_video');
$embed = null;
if ($yt = youtube_id($video)) { $embed = 'https://www.youtube-nocookie.com/embed/' . $yt; }
elseif (preg_match('~vimeo\.com/(\d+)~', $video, $m)) { $embed = 'https://player.vimeo.com/video/' . $m[1]; }
$timeline = [
    ['Mar del Plata', 'Nace en Mar del Plata, crece en Neuquén y vuelve a estudiar cerámica.'],
    ['Formación', 'Licenciada en Terapia Ocupacional (UNMDP), magíster en Gestión de Servicios de Salud.'],
    ['Docencia', 'Más de 30 años como docente e investigadora, y directora de la carrera.'],
    ['Acompañamiento', 'Dos décadas en psicooncología y cuidados paliativos, con niños y familias desde Paanet.'],
    ['Hoy', 'Dirige la Diplomatura en Terapia Ocupacional con personas con cáncer (UCA). Escribe, pinta y comparte su tránsito en la Comunidad. Sus libros ya se leen en Argentina y España.'],
];
?>
<section class="hero">
    <?= partial('partials/washes') ?>
    <div class="container person">
        <div class="person__photo photo-halo"><div class="photo" style="aspect-ratio:460/560"><?= img('fotos/sil-rosa.jpg', 'Silvina Oudshoorn', '') ?></div></div>
        <div>
            <span class="kicker">Sobre Silvina</span>
            <h1 style="font-size:clamp(2.2rem,3.6vw,3.2rem)">Silvina Oudshoorn</h1>
            <span class="hand">“Soy simplemente Silvina, la del apellido difícil y la sonrisa fácil.”</span>
            <p>Terapista ocupacional, docente, artista y autora. Hace 30 años acompaña a las personas a sentirse mejor a través de lo que hacen: crear, escribir, compartir.</p>
            <div class="stats">
                <div class="stat-chip"><strong>30 años</strong><span>acompañando personas</span></div>
                <div class="stat-chip"><strong>7 libros</strong><span>publicados</span></div>
                <div class="stat-chip"><strong>Artista</strong><span>y ceramista</span></div>
            </div>
        </div>
    </div>
</section>

<section class="band band--white">
    <div class="container">
        <div class="band__head"><span class="kicker">Su recorrido</span><h2>Un tránsito entre la salud y el arte</h2></div>
        <ol class="timeline">
            <?php foreach ($timeline as [$t, $d]): ?><li><h3><?= e($t) ?></h3><p><?= e($d) ?></p></li><?php endforeach; ?>
        </ol>
    </div>
</section>

<section class="band">
    <div class="container video-split">
        <div class="video-thumb">
            <?php if ($embed): ?>
                <iframe src="<?= e($embed) ?>" title="¿Qué es la terapia ocupacional?" allow="fullscreen; picture-in-picture" loading="lazy"></iframe>
            <?php else: ?>
                <?= img('fotos/mesa-acuarelas.jpg', 'Video de Silvina (próximamente)', '') ?>
                <span class="play"><?= icon('triangle') ?></span>
            <?php endif; ?>
        </div>
        <div>
            <span class="kicker">Terapia ocupacional</span>
            <h2>¿Qué es la terapia ocupacional?</h2>
            <p class="muted">Un video corto de Silvina para contar, con sus palabras, cómo lo que hacemos nos ayuda a sentirnos mejor.</p>
            <span class="hand" style="font-size:1.25rem">9 minutos, con un cafecito</span>
        </div>
    </div>
</section>

<section class="band band--sky band--tight">
    <div class="container banner-row">
        <div><h2>7 libros sobre salud, arte y acompañamiento</h2><p>Escritos desde su experiencia, para acompañar y ayudar a sentirse mejor. Desde 2007 hasta hoy.</p></div>
        <a class="btn btn--outline" href="/libros">Ver sus libros</a>
    </div>
</section>

<?= partial('partials/testimonials') ?>

<?= partial('partials/cta', ['hand' => 'te espero en la Comunidad', 'title' => 'Transitemos juntos', 'button' => 'Sumate a la Comunidad', 'bg' => 'cream',
    'extra' => '<a class="link-arrow small" href="/contacto">¿Querés escribirle a Silvina? Contacto →</a>']) ?>
