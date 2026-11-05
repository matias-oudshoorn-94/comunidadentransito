<?php
/** @var array $entries @var array $sections @var int $sectionId */
use App\Models\Entry;
?>
<section class="box">
    <div class="box__head" style="flex-wrap:wrap">
        <form class="toolbar" method="get">
            <select class="select" name="seccion" aria-label="Sección" onchange="this.form.submit()">
                <option value="0">Todas las secciones</option>
                <?php foreach ($sections as $s): ?><option value="<?= (int) $s['id'] ?>" <?= $sectionId === (int) $s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option><?php endforeach; ?>
            </select>
        </form>
        <a class="btn btn--sm" href="/admin/contenidos/nueva<?= $sectionId ? '?seccion=' . $sectionId : '' ?>"><?= icon('plus') ?> Nueva entrega</a>
    </div>
    <div class="table-wrap">
        <table class="atable">
            <thead><tr><th>Título</th><th>Sección</th><th>Tipo</th><th>Publicada</th><th>Visibilidad</th></tr></thead>
            <tbody>
                <?php foreach ($entries as $en): ?>
                    <tr>
                        <td><a href="/admin/contenidos/<?= (int) $en['id'] ?>"><?= e($en['title']) ?></a></td>
                        <td><?= e($en['section_name']) ?></td>
                        <td><?= e(Entry::KINDS[$en['kind']] ?? $en['kind']) ?></td>
                        <td><?= fecha_corta($en['published_at']) ?></td>
                        <td>
                            <?php if (!$en['is_published']): ?><span class="badge badge--expired">Borrador</span>
                            <?php elseif ($en['is_preview']): ?><span class="badge badge--approved">Muestra pública</span>
                            <?php else: ?><span class="badge badge--plain">Miembros</span><?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$entries): ?><tr><td colspan="5" class="empty">No hay entregas.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
