<?php /** A6 · Contacto */ $s = config('site.social'); ?>
<section class="hero">
    <?= partial('partials/washes') ?>
    <div class="container contact">
        <div>
            <span class="kicker">Contacto</span>
            <h1>Escribime</h1>
            <span class="hand">me encanta leerte</span>
            <p class="lead">La forma más rápida es por WhatsApp. También podés dejarme un mensaje acá y te respondo en unos días.</p>
            <a class="btn" href="<?= e(whatsapp()) ?>" target="_blank" rel="noopener" style="margin-top:8px">Escribime por WhatsApp</a>
            <ul class="contact-list">
                <li><span class="icon-dot"><?= icon('whatsapp') ?></span><a href="<?= e(whatsapp()) ?>" target="_blank" rel="noopener"><strong>WhatsApp</strong><span><?= e($s['whatsapp_label']) ?></span></a></li>
                <li><span class="icon-dot"><?= icon('instagram') ?></span><a href="<?= e($s['instagram']) ?>" target="_blank" rel="noopener"><strong>Instagram</strong><span><?= e($s['instagram_label']) ?></span></a></li>
                <li><span class="icon-dot"><?= icon('facebook') ?></span><a href="<?= e($s['facebook']) ?>" target="_blank" rel="noopener"><strong>Facebook</strong><span><?= e($s['facebook_label']) ?></span></a></li>
                <li><span class="icon-dot"><?= icon('pin') ?></span><div><strong>Mar del Plata</strong><span>Buenos Aires, Argentina</span></div></li>
            </ul>
        </div>

        <form class="panel form" method="post" action="/contacto" novalidate>
            <?= csrf_field() ?>
            <div class="hp-field" aria-hidden="true"><label>No completar <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
            <div class="panel__title" style="margin:0"><span class="icon-dot"><?= icon('mail') ?></span><h2>Dejame un mensaje</h2></div>
            <div class="field <?= error('name') ? 'has-error' : '' ?>">
                <label for="name">Tu nombre</label>
                <input class="input input--cream" id="name" name="name" value="<?= e(old('name')) ?>" autocomplete="name" placeholder="¿Cómo te llamás?" required>
                <?= partial('partials/field-error', ['name' => 'name']) ?>
            </div>
            <div class="field <?= error('email') ? 'has-error' : '' ?>">
                <label for="email">Tu email</label>
                <input class="input input--cream" id="email" name="email" type="email" value="<?= e(old('email')) ?>" autocomplete="email" placeholder="nombre@correo.com" required>
                <?= partial('partials/field-error', ['name' => 'email']) ?>
            </div>
            <div class="field <?= error('message') ? 'has-error' : '' ?>">
                <label for="message">Tu mensaje</label>
                <textarea class="textarea textarea--cream" id="message" name="message" placeholder="Contame lo que quieras…" required><?= e(old('message')) ?></textarea>
                <?= partial('partials/field-error', ['name' => 'message']) ?>
            </div>
            <button class="btn btn--block" type="submit">Enviar mensaje</button>
            <span class="small muted">Te respondo personalmente, sin apuro.</span>
        </form>
    </div>
</section>
