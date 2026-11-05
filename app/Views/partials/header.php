<?php

use App\Models\Section;

$groups = Section::grouped();
$groupNames = config('site.groups');
$user = auth();
$links = [
    '/la-comunidad'  => 'La Comunidad',
    '/membresias'    => 'Membresías',
    '/libros'        => 'Libros',
    '/sobre-silvina' => 'Sobre Silvina',
    '/contacto'      => 'Contacto',
];
?>
<header class="site-header">
    <div class="container site-header__inner">
        <?= partial('partials/brand') ?>

        <nav class="nav" aria-label="Principal">
            <a class="nav__link <?= is_path('/la-comunidad') ? 'is-active' : '' ?>" href="/la-comunidad">La Comunidad</a>

            <div class="nav__dropdown" data-dropdown>
                <button class="nav__trigger" type="button" aria-expanded="false" aria-controls="mega-contenidos" data-dropdown-trigger>
                    Contenidos para vos <?= icon('chevron') ?>
                </button>
                <div class="mega" id="mega-contenidos" data-dropdown-panel>
                    <?php foreach ($groups as $key => $sections): ?>
                        <div class="mega__group">
                            <h4><?= e($groupNames[$key]) ?></h4>
                            <ul>
                                <?php foreach ($sections as $s): ?>
                                    <li><a href="/contenidos/<?= e($s['slug']) ?>"><?= icon($s['icon'] ?? 'sparkle') ?><?= e($s['name']) ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endforeach; ?>
                    <div class="mega__all">
                        <span class="muted small">Cada sección tiene un adelanto para que la conozcas.</span>
                        <a class="btn btn--soft btn--sm" href="/contenidos">Ver todo <?= icon('arrow', 'icon icon--move') ?></a>
                    </div>
                </div>
            </div>

            <?php foreach (array_slice($links, 1) as $href => $label): ?>
                <a class="nav__link <?= is_path($href) ? 'is-active' : '' ?>" href="<?= $href ?>"><?= e($label) ?></a>
            <?php endforeach; ?>
        </nav>

        <div class="nav__actions">
            <?php if ($user): ?>
                <?php if ($user['role'] === 'admin'): ?>
                    <a class="nav__link" href="/admin">Admin</a>
                <?php endif; ?>
                <a class="btn btn--sm" href="/mi-comunidad"><?= icon('user') ?> Mi Comunidad</a>
            <?php else: ?>
                <a class="nav__link" href="/ingresar">Ingresar</a>
                <a class="btn btn--sm" href="/membresias">Sumarme</a>
            <?php endif; ?>
        </div>

        <button class="menu-toggle" type="button" aria-label="Abrir menú" aria-controls="mobile-menu" aria-expanded="false" data-menu-open>
            <?= icon('menu') ?>
        </button>
    </div>
</header>

<div class="mobile-menu" id="mobile-menu" aria-hidden="true" role="dialog" aria-modal="true" aria-label="Menú">
    <div class="mobile-menu__top">
        <?= partial('partials/brand') ?>
        <button class="menu-toggle" style="display:inline-flex" type="button" aria-label="Cerrar menú" data-menu-close><?= icon('close') ?></button>
    </div>
    <nav class="mobile-menu__body" aria-label="Principal">
        <a class="mobile-menu__link" href="/">Inicio</a>
        <a class="mobile-menu__link" href="/la-comunidad">La Comunidad</a>
        <button class="mobile-menu__trigger" type="button" aria-expanded="false" aria-controls="mm-contenidos" data-accordion>
            Contenidos para vos <?= icon('plus') ?>
        </button>
        <div class="mobile-menu__panel" id="mm-contenidos">
            <div>
                <?php foreach ($groups as $key => $sections): ?>
                    <h4><?= e($groupNames[$key]) ?></h4>
                    <?php foreach ($sections as $s): ?>
                        <a href="/contenidos/<?= e($s['slug']) ?>"><?= e($s['name']) ?></a>
                    <?php endforeach; ?>
                <?php endforeach; ?>
                <a href="/contenidos" style="color:var(--turquesa)">Ver todos los contenidos →</a>
            </div>
        </div>
        <?php foreach (array_slice($links, 1) as $href => $label): ?>
            <a class="mobile-menu__link" href="<?= $href ?>"><?= e($label) ?></a>
        <?php endforeach; ?>

        <div class="mobile-menu__actions">
            <?php if ($user): ?>
                <a class="btn btn--block" href="/mi-comunidad">Mi Comunidad</a>
                <a class="btn btn--ghost btn--block" href="/mi-cuenta">Mi cuenta</a>
            <?php else: ?>
                <a class="btn btn--block" href="/membresias">Sumarme a la comunidad</a>
                <a class="btn btn--ghost btn--block" href="/ingresar">Ingresar</a>
            <?php endif; ?>
        </div>
    </nav>
</div>
