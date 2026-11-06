<?php $s = config('site.social'); ?>
<footer class="site-footer">
    <div class="container">
        <div class="site-footer__grid">
            <div class="site-footer__brand">
                <h2 class="site-footer__logo"><a href="/"><img src="<?= asset('img/logo-sintetico-blanco.webp') ?>" alt="Comunidad en Tránsito" width="400" height="144" loading="lazy"></a></h2>
                <p><?= e(config('site.tagline')) ?></p>
            </div>
            <div>
                <h3><a href="/la-comunidad">Aquí, La Comunidad</a></h3>
                <p class="site-footer__links"><a href="/membresias">Membresías</a> · <a href="/membresias#preguntas">Preguntas frecuentes</a></p>
            </div>
            <div>
                <h3><a href="/contenidos">Contenidos para vos</a></h3>
                <p class="site-footer__links"><a href="/contenidos#crear">Crear y escribir</a> · <a href="/contenidos#leer">Leer, ver y escuchar</a> · <a href="/contenidos#encontrarnos">Encontrarnos</a></p>
            </div>
            <div>
                <h3><a href="/contacto">Contacto</a></h3>
                <p class="site-footer__links"><a href="<?= e(whatsapp()) ?>" target="_blank" rel="noopener">WhatsApp</a> · <a href="<?= e($s['instagram']) ?>" target="_blank" rel="noopener">Instagram</a> · <a href="<?= e($s['facebook']) ?>" target="_blank" rel="noopener">Facebook</a></p>
            </div>
        </div>

        <div class="site-footer__mobile">
            <h2 class="site-footer__logo"><a href="/"><img src="<?= asset('img/logo-sintetico-blanco.webp') ?>" alt="Comunidad en Tránsito" width="400" height="144" loading="lazy"></a></h2>
            <p><?= e(config('site.tagline')) ?></p>
            <p><a href="/la-comunidad">La Comunidad</a> · <a href="/contenidos">Contenidos</a> · <a href="/libros">Libros</a> · <a href="/sobre-silvina">Sobre Silvina</a> · <a href="/contacto">Contacto</a></p>
            <p class="site-footer__social"><a href="<?= e(whatsapp()) ?>" target="_blank" rel="noopener">WhatsApp</a> · <a href="<?= e($s['instagram']) ?>" target="_blank" rel="noopener">Instagram</a> · <a href="<?= e($s['facebook']) ?>" target="_blank" rel="noopener">Facebook</a></p>
        </div>
    </div>
</footer>
