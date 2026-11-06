<?php
/** @var array $result @var string $q @var string $status */
$tabs = ['' => 'Todas las personas', 'activos' => 'Con membresía activa', 'vencidos' => 'Vencidas', 'sin-membresia' => 'Sin membresía'];
$qs = fn (array $extra) => '?' . http_build_query(array_filter(['q' => $q, 'estado' => $status] + $extra) + $extra);
?>
<section class="box">
    <div class="box__head" style="flex-wrap:wrap">
        <nav class="tabs" aria-label="Filtrar">
            <?php foreach ($tabs as $key => $label): ?>
                <a href="?<?= http_build_query(array_filter(['q' => $q, 'estado' => $key])) ?>" class="<?= $status === $key ? 'is-active' : '' ?>"><?= e($label) ?></a>
            <?php endforeach; ?>
        </nav>
        <form class="toolbar" method="get">
            <?php if ($status): ?><input type="hidden" name="estado" value="<?= e($status) ?>"><?php endif; ?>
            <input class="input" type="search" name="q" value="<?= e($q) ?>" placeholder="Buscar por nombre o email" aria-label="Buscar">
            <button class="btn btn--sm" type="submit"><?= icon('search') ?> Buscar</button>
        </form>
    </div>

    <div class="table-wrap">
        <table class="atable">
            <thead><tr><th>Persona</th><th>Membresía</th><th>Estado</th><th>Vence</th><th>Alta</th><th>Último ingreso</th></tr></thead>
            <tbody>
                <?php foreach ($result['rows'] as $u):
                    $expired = $u['membership_status'] === 'active' && $u['ends_at'] < now();
                    $st = $expired ? 'expired' : $u['membership_status']; ?>
                    <tr>
                        <td>
                            <a href="/admin/usuarios/<?= (int) $u['id'] ?>"><?= e($u['name']) ?></a>
                            <span class="sub"><?= e($u['email']) ?></span>
                        </td>
                        <td><?= e($u['plan_name'] ?? '—') ?></td>
                        <td><?= $st ? badge($st) : '<span class="muted">—</span>' ?></td>
                        <td><?= fecha_corta($u['ends_at']) ?><?= $u['auto_renew'] && $st === 'active' ? '<span class="sub">se renueva</span>' : '' ?></td>
                        <td><?= fecha_corta($u['created_at']) ?></td>
                        <td><?= fecha_corta($u['last_login_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$result['rows']): ?><tr><td colspan="6" class="empty">No encontramos personas con ese filtro.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="pager">
        <span class="muted"><?= $result['total'] ?> personas · página <?= $result['page'] ?> de <?= $result['pages'] ?></span>
        <?php if ($result['page'] > 1): ?><a class="btn btn--ghost btn--xs" href="<?= e($qs(['pagina' => $result['page'] - 1])) ?>">← Anterior</a><?php endif; ?>
        <?php if ($result['page'] < $result['pages']): ?><a class="btn btn--ghost btn--xs" href="<?= e($qs(['pagina' => $result['page'] + 1])) ?>">Siguiente →</a><?php endif; ?>
    </div>
</section>
