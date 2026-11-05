<?php /** A6 · Contacto */ ?>
<section class="page-hero">
    <?= partial('partials/washes') ?>
    <div class="container split" style="align-items:start">
        <div>
            <span class="kicker">Contacto</span>
            <h1>Escribime</h1>
            <p class="lead">Si tenés dudas sobre la comunidad, los libros o querés proponer algo, dejame tu mensaje. Te respondo en unos días.</p>
            <ul class="list-plain" style="margin-top:28px">
                <li class="list-row"><span><?= icon('mail') ?></span><a href="mailto:<?= e(config('site.social.email')) ?>"><?= e(config('site.social.email')) ?></a></li>
                <li class="list-row"><span><?= icon('heart') ?></span><a href="<?= e(config('site.social.instagram')) ?>" target="_blank" rel="noopener">Instagram</a></li>
            </ul>
        </div>

        <form class="panel form" method="post" action="/contacto" novalidate>
            <?= csrf_field() ?>
            <div class="hp-field" aria-hidden="true"><label>No completar <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
            <div class="form-row">
                <div class="field <?= error('name') ? 'has-error' : '' ?>">
                    <label for="name">Tu nombre</label>
                    <input class="input" id="name" name="name" value="<?= e(old('name')) ?>" autocomplete="name" required>
                    <?= partial('partials/field-error', ['name' => 'name']) ?>
                </div>
                <div class="field <?= error('email') ? 'has-error' : '' ?>">
                    <label for="email">Tu email</label>
                    <input class="input" id="email" name="email" type="email" value="<?= e(old('email')) ?>" autocomplete="email" required>
                    <?= partial('partials/field-error', ['name' => 'email']) ?>
                </div>
            </div>
            <div class="field">
                <label for="subject">Asunto <span class="muted">(opcional)</span></label>
                <input class="input" id="subject" name="subject" value="<?= e(old('subject')) ?>">
            </div>
            <div class="field <?= error('message') ? 'has-error' : '' ?>">
                <label for="message">Mensaje</label>
                <textarea class="textarea" id="message" name="message" required><?= e(old('message')) ?></textarea>
                <?= partial('partials/field-error', ['name' => 'message']) ?>
            </div>
            <button class="btn" type="submit">Enviar mensaje <?= icon('arrow', 'icon icon--move') ?></button>
        </form>
    </div>
</section>
