<?php

use App\Models\ContactMessage;

$user = auth();
$unread = ContactMessage::unreadCount();
$nav = [
    ['/admin', 'Resumen', 'home'],
    ['/admin/usuarios', 'Usuarios', 'users'],
    ['/admin/membresias', 'Membresías', 'layers'],
    ['/admin/pagos', 'Pagos', 'card'],
    ['/admin/calendario', 'Calendario', 'calendar'],
    ['/admin/contenidos', 'Contenidos', 'book'],
    ['/admin/secciones', 'Secciones y acceso', 'folder'],
    ['/admin/planes', 'Planes', 'tag'],
    ['/admin/mensajes', 'Mensajes', 'mail'],
];
?>
<!doctype html>
<html lang="es-AR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <link rel="icon" type="image/svg+xml" href="<?= asset('img/logo-mark.svg') ?>">
    <title><?= e(($title ?? 'Admin') . ' · Admin · ' . config('app.name')) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600&family=JetBrains+Mono:wght@500&family=Nunito+Sans:opsz,wght@6..12,400;6..12,700&display=swap">
    <link rel="stylesheet" href="<?= asset('css/base.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/components.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
    <script src="<?= asset('js/app.js') ?>" defer></script>
    <script src="<?= asset('js/admin.js') ?>" defer></script>
</head>
<body class="admin">
    <a class="skip-link" href="#main">Saltar al contenido</a>
    <aside class="admin-side" id="admin-side">
        <a class="admin-brand" href="/admin">
            <img src="<?= asset('img/logo-mark.svg') ?>" alt="" width="36" height="36">
            <span>Comunidad<br><small>Administración</small></span>
        </a>
        <nav class="admin-nav" aria-label="Administración">
            <?php foreach ($nav as [$href, $label, $icon]): ?>
                <a href="<?= $href ?>" class="<?= ($href === '/admin' ? current_path() === '/admin' : is_path($href)) ? 'is-active' : '' ?>">
                    <?= icon($icon) ?><span><?= e($label) ?></span>
                    <?php if ($href === '/admin/mensajes' && $unread): ?><span class="admin-badge"><?= $unread ?></span><?php endif; ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="admin-side__foot">
            <a href="/" target="_blank" rel="noopener"><?= icon('external') ?> Ver el sitio</a>
            <form method="post" action="/salir"><?= csrf_field() ?><button type="submit"><?= icon('logout') ?> Salir</button></form>
        </div>
    </aside>

    <div class="admin-main">
        <header class="admin-top">
            <button class="admin-menu-btn" type="button" aria-label="Menú" onclick="document.body.classList.toggle('side-open')"><?= icon('menu') ?></button>
            <h1><?= e($title ?? 'Admin') ?></h1>
            <span class="admin-top__user"><?= icon('user') ?> <?= e($user['name']) ?></span>
        </header>
        <main id="main" class="admin-content">
            <?= $content ?>
        </main>
    </div>

    <div id="toasts" class="toasts" aria-live="polite"></div>
    <?php if ($toast = flash('toast')): ?><span hidden data-toast="<?= e($toast) ?>"></span><?php endif; ?>
</body>
</html>
