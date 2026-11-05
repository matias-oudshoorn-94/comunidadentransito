<?php

use App\Models\Section;
use App\Services\AccessService;

$user = auth();
$member = $user && AccessService::isMember($user);
$groups = Section::grouped();
$groupNames = config('site.groups');
// "Contenidos para vos" queda marcado en cualquier sección, salvo las que tienen su propio link en el menú
$contentActive = is_path('/contenidos') && !($member && (is_path('/contenidos/runas-de-letras') || is_path('/contenidos/cafeteria-en-transito')));
$mode = $headerMode ?? 'site';

// Con membresía el menú cambia (Figma: Mi Comunidad, Runas, Cafetería)
$links = $member
    ? ['/mi-comunidad' => 'Mi Comunidad', 'contenidos' => null, '/contenidos/runas-de-letras' => 'Runas de Letras', '/contenidos/cafeteria-en-transito' => 'Cafetería en tránsito', '/libros' => 'Libros']
    : ['/la-comunidad' => 'Aquí, La Comunidad', 'contenidos' => null, '/libros' => 'Libros', '/sobre-silvina' => 'Sobre Silvina', '/contacto' => 'Contacto'];
?>
<header class="site-header">
    <div class="container site-header__inner">
        <?= partial('partials/brand') ?>

        <?php if ($mode === 'checkout'): ?>
            <span class="secure"><?= icon('lock') ?> Pago seguro</span>
        <?php else: ?>
            <nav class="nav" aria-label="Principal">
                <?php foreach ($links as $href => $label): ?>
                    <?php if ($href === 'contenidos'): ?>
                        <div class="nav__dropdown" data-dropdown>
                            <button class="nav__trigger <?= $contentActive ? 'is-active' : '' ?>" type="button" aria-expanded="false" aria-controls="mega-contenidos" data-dropdown-trigger>
                                Contenidos para vos <?= icon('chevron') ?>
                            </button>
                            <div class="mega" id="mega-contenidos" data-dropdown-panel>
                                <?php foreach ($groups as $key => $sections): ?>
                                    <div class="mega__group">
                                        <h4><?= e($groupNames[$key]) ?></h4>
                                        <ul>
                                            <?php foreach ($sections as $s): ?>
                                                <li><a href="/contenidos/<?= e($s['slug']) ?>"><?= e($s['name']) ?></a></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endforeach; ?>
                                <div class="mega__all">
                                    <span class="muted small">Cada sección tiene un adelanto para que la conozcas.</span>
                                    <a class="btn btn--soft btn--sm" href="/contenidos">Ver todo</a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <a class="nav__link <?= is_path($href) ? 'is-active' : '' ?>" href="<?= $href ?>"><?= e($label) ?></a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </nav>

            <div class="nav__actions">
                <?php if ($user): ?>
                    <div class="user-menu" data-dropdown>
                        <button class="user-menu__trigger" type="button" aria-expanded="false" aria-controls="user-menu" data-dropdown-trigger>
                            <?= avatar($user) ?> <?= e(first_name($user['name'])) ?>
                        </button>
                        <div class="user-menu__panel" id="user-menu" data-dropdown-panel>
                            <a href="/mi-comunidad">Mi Comunidad</a>
                            <a href="/mi-cuenta">Mi cuenta</a>
                            <?php if ($user['role'] === 'admin'): ?><a href="/admin">Administración</a><?php endif; ?>
                            <?php if (!$member): ?><a href="/membresias">Membresías</a><?php endif; ?>
                            <form method="post" action="/salir"><?= csrf_field() ?><button type="submit">Cerrar sesión</button></form>
                        </div>
                    </div>
                <?php else: ?>
                    <a class="nav__login" href="/ingresar">Ingresar</a>
                    <a class="btn btn--sm" href="/membresias">Sumate</a>
                <?php endif; ?>
            </div>

            <div class="mobile-actions">
                <?php if ($user): ?>
                    <a href="/mi-cuenta" aria-label="Mi cuenta"><?= avatar($user) ?></a>
                <?php else: ?>
                    <a class="btn btn--sm" href="/membresias">Sumate</a>
                <?php endif; ?>
                <button class="menu-toggle" type="button" aria-label="Abrir menú" aria-controls="mobile-menu" aria-expanded="false" data-menu-open><?= icon('menu') ?></button>
            </div>
        <?php endif; ?>
    </div>
</header>

<?php if ($mode !== 'checkout'): ?>
<div class="mobile-menu" id="mobile-menu" aria-hidden="true" role="dialog" aria-modal="true" aria-label="Menú">
    <div class="mobile-menu__top">
        <?= partial('partials/brand') ?>
        <button class="menu-toggle" style="display:flex" type="button" aria-label="Cerrar menú" data-menu-close><?= icon('close') ?></button>
    </div>
    <nav class="mobile-menu__body" aria-label="Principal">
        <?php foreach ($links as $href => $label): ?>
            <?php if ($href === 'contenidos'): ?>
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
            <?php else: ?>
                <a class="mobile-menu__link" href="<?= $href ?>"><?= e($label) ?></a>
            <?php endif; ?>
        <?php endforeach; ?>

        <div class="mobile-menu__actions">
            <?php if ($user): ?>
                <a class="btn btn--block" href="/mi-cuenta">Mi cuenta</a>
                <form method="post" action="/salir"><?= csrf_field() ?><button class="btn btn--ghost btn--block" type="submit">Cerrar sesión</button></form>
            <?php else: ?>
                <a class="btn btn--block" href="/membresias">Sumate a la Comunidad</a>
                <a class="btn btn--ghost btn--block" href="/ingresar">Ingresar</a>
            <?php endif; ?>
        </div>
    </nav>
</div>
<?php endif; ?>
