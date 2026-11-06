<?php
/** @var array $entries @var array $sections @var array $counts @var int $sectionId @var string $status @var string $search */
use App\Models\Entry;

// Arma el link de un filtro conservando los demás. "seccion" va siempre: sin parámetros se vuelve al último filtro guardado.
$link = function (array $change) use ($sectionId, $status, $search): string {
    $params = array_merge(['seccion' => $sectionId, 'estado' => $status, 'q' => $search], $change);
    $params = array_filter($params, fn ($v) => $v !== '' && $v !== null);
    $params['seccion'] ??= 0;
    return '/admin/contenidos?' . http_build_query($params);
};
$groupNames = config('site.groups');
$bySection = [];
foreach ($sections as $s) {
    $bySection[$s['group_key']][] = $s;
}
$filtered = $sectionId > 0 || $status !== '' || $search !== '';
$current = null;
foreach ($sections as $s) {
    if ((int) $s['id'] === $sectionId) {
        $current = $s;
    }
}
?>
<section class="box">
    <div class="box__head" style="flex-wrap:wrap">
        <form class="efilter__search" method="get" action="/admin/contenidos" role="search" data-entry-search>
            <input type="hidden" name="seccion" value="<?= $sectionId ?>">
            <?php if ($status !== ''): ?><input type="hidden" name="estado" value="<?= e($status) ?>"><?php endif; ?>
            <?= icon('search') ?>
            <input class="input" type="search" name="q" value="<?= e($search) ?>" placeholder="Buscar por título…" aria-label="Buscar por título" <?= $search !== '' ? 'autofocus' : '' ?>>
        </form>
        <a class="btn btn--sm" href="/admin/contenidos/nueva<?= $sectionId ? '?seccion=' . $sectionId : '' ?>"><?= icon('plus') ?> Nueva entrega<?= $current ? ' en ' . e($current['name']) : '' ?></a>
    </div>

    <nav class="efilter" aria-label="Filtrar por sección">
        <div class="efilter__row">
            <a class="chipf <?= $sectionId === 0 ? 'is-on' : '' ?>" href="<?= e($link(['seccion' => 0])) ?>" <?= $sectionId === 0 ? 'aria-current="true"' : '' ?>>
                Todas <span><?= array_sum($counts) ?></span>
            </a>
        </div>
        <?php foreach ($bySection as $key => $list): ?>
            <div class="efilter__row">
                <span class="efilter__label"><?= e($groupNames[$key] ?? $key) ?></span>
                <?php foreach ($list as $s): $n = $counts[(int) $s['id']] ?? 0; $on = (int) $s['id'] === $sectionId; ?>
                    <a class="chipf <?= $on ? 'is-on' : '' ?> <?= $n === 0 && !$on ? 'is-empty' : '' ?>" href="<?= e($link(['seccion' => (int) $s['id']])) ?>" <?= $on ? 'aria-current="true"' : '' ?>>
                        <?= e($s['name']) ?> <span><?= $n ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </nav>

    <div class="efilter__bar">
        <div class="seg" role="group" aria-label="Filtrar por estado">
            <a class="<?= $status === '' ? 'is-on' : '' ?>" href="<?= e($link(['estado' => ''])) ?>">Todos los estados</a>
            <?php foreach (Entry::ADMIN_STATUSES as $key => $label): ?>
                <a class="<?= $status === $key ? 'is-on' : '' ?>" href="<?= e($link(['estado' => $key])) ?>"><?= e($label) ?></a>
            <?php endforeach; ?>
        </div>
        <span class="muted small">
            <?= count($entries) ?> <?= count($entries) === 1 ? 'entrega' : 'entregas' ?>
            <?php if ($filtered): ?> · <a href="<?= e('/admin/contenidos?seccion=0') ?>">Quitar filtros</a><?php endif; ?>
        </span>
    </div>

    <div class="table-wrap">
        <table class="atable">
            <thead><tr><th>Título</th><?php if (!$sectionId): ?><th>Sección</th><?php endif; ?><th>Tipo</th><th>Publicada</th><th>Visibilidad</th></tr></thead>
            <tbody>
                <?php foreach ($entries as $en): ?>
                    <tr>
                        <td><a href="/admin/contenidos/<?= (int) $en['id'] ?>"><?= e($en['title']) ?></a></td>
                        <?php if (!$sectionId): ?><td><?= e($en['section_name']) ?></td><?php endif; ?>
                        <td><?= e(Entry::KINDS[$en['kind']] ?? $en['kind']) ?></td>
                        <td><?= fecha_corta($en['published_at']) ?></td>
                        <td>
                            <?php if (!$en['is_published']): ?><span class="badge badge--expired">Borrador</span>
                            <?php elseif ($en['is_preview']): ?><span class="badge badge--approved">Muestra pública</span>
                            <?php else: ?><span class="badge badge--plain">Miembros</span><?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$entries): ?><tr><td colspan="5" class="empty"><?= $filtered ? 'No hay entregas con este filtro.' : 'No hay entregas.' ?></td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
