<?php
/** @var string $content */
$pageTitle = isset($title) && $title ? $title . ' · ' . config('app.name') : config('app.name') . ' · ' . config('site.tagline');
$description = $description ?? 'Comunidad de escritura, lectura y encuentros con Silvina. ' . config('site.tagline');
?>
<!doctype html>
<html lang="es-AR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <meta name="theme-color" content="#FBF9F1">
    <meta name="description" content="<?= e($description) ?>">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e($description) ?>">
    <meta property="og:image" content="<?= e(absolute_url('assets/img/og-image.jpg')) ?>">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="icon" type="image/svg+xml" href="<?= asset('img/logo-mark.svg') ?>">
    <title><?= e($pageTitle) ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Caveat:wght@500&family=Fraunces:ital,opsz,wght@0,9..144,600;1,9..144,600&family=JetBrains+Mono:wght@500&family=Nunito+Sans:ital,opsz,wght@0,6..12,400;0,6..12,700;1,6..12,400&display=swap">

    <link rel="stylesheet" href="<?= asset('css/base.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/layout.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/components.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/pages.css') ?>">
    <script src="<?= asset('js/app.js') ?>" defer></script>
</head>
<body class="<?= e($bodyClass ?? '') ?>">
    <a class="skip-link" href="#main">Saltar al contenido</a>

    <?= partial('partials/header') ?>

    <main id="main">
        <?= $content ?>
    </main>

    <?= partial('partials/footer') ?>

    <div id="toasts" class="toasts" aria-live="polite"></div>
    <?php if ($toast = flash('toast')): ?>
        <span hidden data-toast="<?= e($toast) ?>"></span>
    <?php endif; ?>
</body>
</html>
