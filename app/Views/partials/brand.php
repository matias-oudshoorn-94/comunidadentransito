<?php
// Logo original de Sil. Cuando esté el logo sintético para celular,
// subilo como public/assets/img/logo-sintetico.svg y se usa automáticamente.
$synthetic = asset_exists('img/logo-sintetico.svg');
?>
<a class="brand <?= $synthetic ? 'brand--has-mark' : '' ?>" href="/" aria-label="<?= e(config('app.name')) ?> · inicio">
    <img class="brand__logo" src="<?= asset('img/logo-header.webp') ?>" alt="" width="300" height="195">
    <?php if ($synthetic): ?>
        <img class="brand__mark" src="<?= asset('img/logo-sintetico.svg') ?>" alt="" width="40" height="40">
    <?php endif; ?>
</a>
