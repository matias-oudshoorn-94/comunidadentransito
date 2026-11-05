<?php

use App\Models\Section;

$groups = Section::grouped();
$user = auth();
?>
<footer class="site-footer">
    <span class="wash wash--light" style="top:-200px;right:-120px;--size:520px" aria-hidden="true"></span>
    <div class="container">
        <div class="site-footer__grid">
            <div class="site-footer__brand">
                <img class="site-footer__logo" src="<?= asset('img/logo-footer.webp') ?>" alt="<?= e(config('app.name')) ?>" width="520" height="338" loading="lazy">
                <p><?= e(config('site.tagline')) ?></p>
                <span class="hand">con cariño, Sil</span>
            </div>
            <div>
                <h4>La comunidad</h4>
                <ul>
                    <li><a href="/la-comunidad">La Comunidad</a></li>
                    <li><a href="/membresias">Membresías</a></li>
                    <li><a href="/libros">Libros</a></li>
                    <li><a href="/sobre-silvina">Sobre Silvina</a></li>
                </ul>
            </div>
            <div>
                <h4>Contenidos</h4>
                <ul>
                    <?php foreach (array_slice(array_merge(...array_values($groups)), 0, 6) as $s): ?>
                        <li><a href="/contenidos/<?= e($s['slug']) ?>"><?= e($s['name']) ?></a></li>
                    <?php endforeach; ?>
                    <li><a href="/contenidos">Ver todo →</a></li>
                </ul>
            </div>
            <div>
                <h4>Hablemos</h4>
                <ul>
                    <li><a href="/contacto">Contacto</a></li>
                    <li><a href="mailto:<?= e(config('site.social.email')) ?>"><?= e(config('site.social.email')) ?></a></li>
                    <li><a href="<?= e(config('site.social.instagram')) ?>" rel="noopener" target="_blank">Instagram</a></li>
                    <?php if ($user): ?>
                        <li><a href="/mi-cuenta">Mi cuenta</a></li>
                    <?php else: ?>
                        <li><a href="/ingresar">Ingresar</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
        <div class="site-footer__bottom">
            <span>© <?= date('Y') ?> <?= e(config('app.name')) ?></span>
            <span>Hecho sin apuro, frente al mar.</span>
        </div>
    </div>
</footer>
