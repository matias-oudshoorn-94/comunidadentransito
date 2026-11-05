<?php /** @var array $payments @var array $filters @var array $totals */ ?>
<section class="box">
    <form class="toolbar" method="get">
        <div class="field"><label for="estado">Estado</label>
            <select class="select" id="estado" name="estado">
                <option value="">Todos</option>
                <?php foreach (['approved', 'pending', 'rejected', 'refunded'] as $s): ?><option value="<?= $s ?>" <?= $filters['estado'] === $s ? 'selected' : '' ?>><?= estado($s) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="field"><label for="medio">Medio</label>
            <select class="select" id="medio" name="medio">
                <option value="">Todos</option>
                <?php foreach (['mercadopago', 'paypal', 'manual'] as $g): ?><option value="<?= $g ?>" <?= $filters['medio'] === $g ? 'selected' : '' ?>><?= medio($g) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="field"><label for="desde">Desde</label><input class="input" type="date" id="desde" name="desde" value="<?= e($filters['desde']) ?>"></div>
        <div class="field"><label for="hasta">Hasta</label><input class="input" type="date" id="hasta" name="hasta" value="<?= e($filters['hasta']) ?>"></div>
        <button class="btn btn--sm" type="submit">Filtrar</button>
        <a class="btn btn--ghost btn--sm" href="/admin/pagos">Limpiar</a>
    </form>
</section>

<div class="stats">
    <div class="stat"><span class="stat__label">Aprobado en pesos</span><span class="stat__value"><?= money($totals['ARS'] ?? 0) ?></span></div>
    <div class="stat"><span class="stat__label">Aprobado en dólares</span><span class="stat__value"><?= money($totals['USD'] ?? 0, 'USD') ?></span></div>
    <div class="stat"><span class="stat__label">Pagos listados</span><span class="stat__value"><?= count($payments) ?></span></div>
</div>

<section class="box">
    <div class="table-wrap">
        <table class="atable">
            <thead><tr><th>Fecha</th><th>Persona</th><th>Plan</th><th>Medio</th><th>Referencia</th><th class="num">Monto</th><th>Estado</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($payments as $p): ?>
                    <tr>
                        <td><?= fecha_corta($p['paid_at'] ?? $p['created_at']) ?></td>
                        <td><a href="/admin/usuarios/<?= (int) $p['user_id'] ?>"><?= e($p['user_name']) ?></a><span class="sub"><?= e($p['user_email']) ?></span></td>
                        <td><?= e($p['plan_name']) ?><?= $p['note'] ? '<span class="sub">' . e($p['note']) . '</span>' : '' ?></td>
                        <td><?= e(medio($p['gateway'])) ?></td>
                        <td><span class="sub"><?= e($p['gateway_payment_id'] ?? $p['gateway_reference'] ?? '—') ?></span></td>
                        <td class="num"><?= money($p['amount'], $p['currency']) ?></td>
                        <td><?= badge($p['status']) ?></td>
                        <td>
                            <?php if ($p['status'] === 'pending'): ?>
                                <form class="inline-form" method="post" action="/admin/pagos/<?= (int) $p['id'] ?>" data-confirm="¿Marcar este pago como aprobado? Se activa la membresía.">
                                    <?= csrf_field() ?><button class="btn btn--soft btn--xs" name="action" value="approve">Aprobar</button>
                                </form>
                                <form class="inline-form" method="post" action="/admin/pagos/<?= (int) $p['id'] ?>">
                                    <?= csrf_field() ?><button class="btn btn--ghost btn--xs" name="action" value="reject">Rechazar</button>
                                </form>
                            <?php elseif ($p['status'] === 'approved'): ?>
                                <form class="inline-form" method="post" action="/admin/pagos/<?= (int) $p['id'] ?>" data-confirm="¿Marcar como reintegrado? (El reintegro se hace en la pasarela.)">
                                    <?= csrf_field() ?><button class="btn btn--ghost btn--xs" name="action" value="refund">Reintegrado</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$payments): ?><tr><td colspan="8" class="empty">No hay pagos con este filtro.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
