<?php
/** @var int $active @var int $orilla @var int $inmersion @var array $revenue @var int $newUsers @var int $pending @var int $unread
 *  @var array $expiring @var int $expiringCount @var array $payments @var array $months */
$max = max(1, ...array_column($months, 'ars'));
?>
<div class="stats">
    <a class="stat" href="/admin/membresias"><span class="stat__label">Miembros activos</span><span class="stat__value"><?= $active ?></span><span class="stat__hint">Orilla <?= $orilla ?> · Inmersión <?= $inmersion ?></span></a>
    <a class="stat" href="/admin/pagos?estado=approved&desde=<?= date('Y-m-01') ?>"><span class="stat__label">Cobrado este mes</span><span class="stat__value"><?= money($revenue['ARS'] ?? 0) ?></span><span class="stat__hint">+ <?= money($revenue['USD'] ?? 0, 'USD') ?> por PayPal</span></a>
    <a class="stat" href="/admin/membresias?estado=por-vencer"><span class="stat__label">Vencen en 30 días</span><span class="stat__value"><?= $expiringCount ?></span><span class="stat__hint">Ver en el calendario</span></a>
    <a class="stat" href="/admin/usuarios"><span class="stat__label">Altas este mes</span><span class="stat__value"><?= $newUsers ?></span><span class="stat__hint"><?= $pending ?> pagos pendientes · <?= $unread ?> mensajes</span></a>
</div>

<div class="cols cols--wide">
    <section class="box">
        <div class="box__head"><h2>Cobrado en pesos, últimos 6 meses</h2><a class="small" href="/admin/pagos">Ver pagos →</a></div>
        <div class="chart" role="img" aria-label="Gráfico de cobros mensuales en pesos. Los valores están en la tabla de abajo.">
            <?php foreach ($months as $m): ?>
                <div class="chart__col" tabindex="0">
                    <span class="chart__tip"><?= e($m['label']) ?>: <?= money($m['ars']) ?><br><?= money($m['usd'], 'USD') ?> por PayPal</span>
                    <span class="chart__bar" style="height:<?= round($m['ars'] / $max * 100, 1) ?>%"></span>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="chart__labels" aria-hidden="true"><?php foreach ($months as $m): ?><span><?= e($m['label']) ?></span><?php endforeach; ?></div>
        <details style="margin-top:14px">
            <summary class="small muted" style="cursor:pointer">Ver como tabla</summary>
            <table class="atable" style="margin-top:8px">
                <thead><tr><th>Mes</th><th class="num">Pesos</th><th class="num">Dólares</th></tr></thead>
                <tbody><?php foreach ($months as $m): ?><tr><td><?= e($m['label']) ?></td><td class="num"><?= money($m['ars']) ?></td><td class="num"><?= money($m['usd'], 'USD') ?></td></tr><?php endforeach; ?></tbody>
            </table>
        </details>
    </section>

    <section class="box">
        <div class="box__head"><h2>Próximos vencimientos</h2><a class="small" href="/admin/calendario">Calendario →</a></div>
        <?php if ($expiring): ?>
            <table class="atable">
                <tbody>
                    <?php foreach ($expiring as $m): ?>
                        <tr>
                            <td><a href="/admin/usuarios/<?= (int) $m['user_id'] ?>"><?= e($m['user_name']) ?></a><span class="sub"><?= e($m['plan_name']) ?><?= $m['auto_renew'] ? ' · se renueva' : '' ?></span></td>
                            <td class="num"><?= fecha_corta($m['ends_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="empty">Nada vence en los próximos 30 días.</p>
        <?php endif; ?>
    </section>
</div>

<section class="box">
    <div class="box__head"><h2>Últimos pagos</h2><a class="small" href="/admin/pagos">Ver todos →</a></div>
    <div class="table-wrap">
        <table class="atable">
            <thead><tr><th>Fecha</th><th>Persona</th><th>Plan</th><th>Medio</th><th class="num">Monto</th><th>Estado</th></tr></thead>
            <tbody>
                <?php foreach ($payments as $p): ?>
                    <tr>
                        <td><?= fecha_corta($p['paid_at'] ?? $p['created_at']) ?></td>
                        <td><a href="/admin/usuarios/<?= (int) $p['user_id'] ?>"><?= e($p['user_name']) ?></a></td>
                        <td><?= e($p['plan_name']) ?></td>
                        <td><?= e(medio($p['gateway'])) ?></td>
                        <td class="num"><?= money($p['amount'], $p['currency']) ?></td>
                        <td><?= badge($p['status']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$payments): ?><tr><td colspan="6" class="empty">Todavía no hay pagos.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
