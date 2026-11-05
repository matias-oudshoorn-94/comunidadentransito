<?php /** @var array $plans */ ?>
<div class="notice"><?= icon('tag') ?><span>Los cambios de precio se aplican a las compras nuevas. Las suscripciones de Mercado Pago ya creadas mantienen su monto.</span></div>
<div class="cols">
    <?php foreach ($plans as $p): ?>
        <form class="box form" method="post" action="/admin/planes/<?= (int) $p['id'] ?>">
            <?= csrf_field() ?>
            <div class="box__head" style="margin:0">
                <h2><?= e($p['name']) ?></h2>
                <span class="badge badge--plain"><?= e(config("site.tiers.{$p['tier']}.name")) ?> · <?= (int) $p['months'] ?> <?= (int) $p['months'] === 1 ? 'mes' : 'meses' ?></span>
            </div>
            <div class="field"><label>Nombre</label><input class="input" name="name" value="<?= e($p['name']) ?>"></div>
            <div class="form-row">
                <div class="field"><label>Precio en pesos</label><input class="input" name="price_ars" type="number" step="0.01" min="0" value="<?= e($p['price_ars']) ?>"></div>
                <div class="field"><label>Precio en dólares</label><input class="input" name="price_usd" type="number" step="0.01" min="0" value="<?= e($p['price_usd']) ?>"></div>
            </div>
            <div class="field"><label>Descripción</label><input class="input" name="description" value="<?= e($p['description']) ?>"></div>
            <label class="check"><input type="checkbox" name="is_active" value="1" <?= $p['is_active'] ? 'checked' : '' ?>> A la venta</label>
            <button class="btn btn--sm" type="submit">Guardar</button>
        </form>
    <?php endforeach; ?>
</div>
