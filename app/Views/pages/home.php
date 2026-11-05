<?php
/** A1 · Portada  @var array $periods @var array $faq @var array $highlights @var array $tiers */
?>
<section class="home-hero">
    <div class="home-hero__photo"><?= img('fotos/home-hero.jpg', 'Silvina sonriendo', '') ?></div>
    <div class="home-hero__text">
        <h1>Un espacio pensado para vos.</h1>
        <p class="lead">Una comunidad para crear, escribir y encontrarte con otros, a tu ritmo, para sentirte mejor.</p>
        <div class="actions">
            <a class="btn" href="/membresias">Quiero sumarme</a>
            <a class="link-arrow" href="#que-incluye">Ver qué incluye →</a>
        </div>
    </div>
</section>

<?= partial('partials/community-blocks', ['checksBg' => 'sky']) ?>

<section class="band" id="que-incluye">
    <div class="container">
        <div class="band__head"><span class="kicker">Contenidos</span><h2>Explorá lo que hay dentro</h2></div>
        <div class="filters" data-filters="explorar" role="group" aria-label="Filtrar contenidos" style="margin-bottom:28px">
            <button class="filter" type="button" data-filter="todo" aria-pressed="true">Todo</button>
            <?php foreach (config('site.groups') as $key => $name): ?>
                <button class="filter" type="button" data-filter="<?= e($key) ?>" aria-pressed="false"><?= e($name) ?></button>
            <?php endforeach; ?>
        </div>
        <div class="grid grid--3" id="explorar">
            <?php foreach ($highlights as $h): ?>
                <?= partial('partials/section-card', $h + ['open' => array_intersect($tiers, $h['section']['tiers']) !== []]) ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="band band--white">
    <div class="container person">
        <div class="person__photo photo-halo"><div class="photo"><?= img('fotos/sil-retrato.jpg', 'Silvina Oudshoorn', '') ?></div></div>
        <div>
            <span class="kicker">Quién te acompaña</span>
            <h2>Silvina Oudshoorn</h2>
            <span class="hand">“Apellido difícil, sonrisa fácil”</span>
            <p>Terapista ocupacional, docente universitaria, artista y autora. Hace 30 años acompaña a las personas a sentirse mejor a través de lo que hacen. Dirige una diplomatura en la UCA y sus libros ya se leen también en España.</p>
            <div class="stats">
                <div class="stat-chip"><strong>30 años</strong><span>acompañando personas</span></div>
                <div class="stat-chip"><strong>7 libros</strong><span>publicados</span></div>
                <div class="stat-chip"><strong>Directora</strong><span>diplomatura UCA</span></div>
            </div>
            <a class="link-arrow" href="/sobre-silvina">Conocé su historia →</a>
        </div>
    </div>
</section>

<section class="band band--sky" id="planes">
    <div class="container">
        <div class="band__head center"><span class="kicker">Membresías</span><h2>Elegí cómo sumarte</h2></div>
        <?= partial('partials/plans', ['periods' => $periods]) ?>
        <p class="plans-note">Cancelás cuando quieras · Pagos con Mercado Pago</p>
    </div>
</section>

<?= partial('partials/testimonials') ?>

<section class="band band--white">
    <div class="container faq-split">
        <div><span class="kicker">Preguntas frecuentes</span><h2>Lo que suelen preguntar antes de sumarse</h2></div>
        <?= partial('partials/faq', ['faq' => $faq]) ?>
    </div>
</section>

<section class="band" id="regalo">
    <div class="container">
        <div class="gift">
            <div>
                <span class="hand">un regalo para vos</span>
                <h2>Tu primera Tecniquita es de regalo</h2>
                <p>Dejá tu mail y te mandamos de regalo una propuesta creativa de Silvina, más las novedades del lanzamiento. Sin compromiso.</p>
            </div>
            <form class="gift-form" method="post" action="/contenidos/tecniquitas/regalo" data-ajax>
                <?= csrf_field() ?>
                <label class="visually-hidden" for="gift-email">Tu email</label>
                <input class="input" id="gift-email" name="email" type="email" required autocomplete="email" placeholder="Tu email">
                <button class="btn btn--block" type="submit">Quiero recibirla</button>
                <span class="note">Podés darte de baja cuando quieras.</span>
            </form>
        </div>
    </div>
</section>

<?= partial('partials/cta', ['title' => '¿Te animás a transitar con nosotros?', 'button' => 'Sumate a la Comunidad', 'bg' => 'sky', 'whatsapp' => true]) ?>
