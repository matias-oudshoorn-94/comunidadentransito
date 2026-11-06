<?php /** Layout del ingreso a la administración: distinto del sitio, sin menú */ ?>
<!doctype html>
<html lang="es-AR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <link rel="icon" type="image/svg+xml" href="<?= asset('img/logo-mark.svg') ?>">
    <title><?= e(($title ?? 'Administración') . ' · ' . config('app.name')) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600&family=JetBrains+Mono:wght@500&family=Nunito+Sans:opsz,wght@6..12,400;6..12,700&display=swap">
    <link rel="stylesheet" href="<?= asset('css/base.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/components.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
    <script src="<?= asset('js/app.js') ?>" defer></script>
</head>
<body class="admin-auth">
    <main id="main"><?= $content ?></main>
    <div id="toasts" class="toasts" aria-live="polite"></div>
    <?php if ($toast = flash('toast')): ?><span hidden data-toast="<?= e($toast) ?>"></span><?php endif; ?>
</body>
</html>
