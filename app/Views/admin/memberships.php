<?php
/** @var array $memberships @var string $status @var string $tier */
$tabs = ['active' => 'Activas', 'por-vencer' => 'Vencen en 30 días', 'expired' => 'Vencidas', 'cancelled' => 'Canceladas', 'todas' => 'Todas'];
?>
<section class="box">
    <div class="box__head" style="flex-wrap:wrap">
        <nav class="tabs" aria-label="Estado">
            <?php foreach ($tabs as $key => $label): ?>
                <a href="?<?= http_build_query(array_filter(['estado' => $key, 'nivel' => $tier])) ?>" class="<?= $status === $key ? 'is-active' : '' ?>"><?= e($label) ?></a>
            <?php endforeach; ?>
        </nav>
        <nav class="tabs" aria-label="Nivel">
            <a href="?<?= http_build_query(['estado' => $status]) ?>" class="<?= $tier === '' ? 'is-active' : '' ?>">Todos los niveles</a>
            <?php foreach (config('site.tiers') as $key => $t): ?>
                <a href="?<?= http_build_query(['estado' => $status, 'nivel' => $key]) ?>" class="<?= $tier === $key ? 'is-active' : '' ?>"><?= e($t['name']) ?></a>
            <?php endforeach; ?>
        </nav>
    </div>
    <div class="table-wrap">
        <table class="atable">
            <thead><tr><th>Persona</th><th>Plan</th><th>Desde</th><th>Vence</th><th>Medio</th><th>Estado</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($memberships as $m): ?>
                    <tr>
                        <td><a href="/admin/usuarios/<?= (int) $m['user_id'] ?>"><?= e($m['user_name']) ?></a><span class="sub"><?= e($m['user_email']) ?></span></td>
                        <td><?= e($m['plan_name']) ?></td>
                        <td><?= fecha_corta($m['starts_at']) ?></td>
                        <td><?= fecha_corta($m['ends_at']) ?></td>
                        <td><?= e(medio($m['gateway'])) ?><?= $m['auto_renew'] ? '<span class="sub">se renueva</span>' : '' ?></td>
                        <td><?= badge($m['status']) ?></td>
                        <td>
                            <form class="inline-form" method="post" action="/admin/membresias/<?= (int) $m['id'] ?>">
                                <?= csrf_field() ?><input type="hidden" name="action" value="extend"><input type="hidden" name="months" value="1">
                                <button class="btn btn--soft btn--xs" type="submit">+1 mes</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$memberships): ?><tr><td colspan="7" class="empty">No hay membresías con este filtro.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <p class="small muted" style="margin:14px 0 0"><?= count($memberships) ?> membresías</p>
</section>
