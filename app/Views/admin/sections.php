<?php /** @var array $groups */ $tiers = config('site.tiers'); ?>
<div class="notice"><?= icon('lock') ?><span>Marcá qué niveles abren cada sección. Quien no tenga acceso ve solo la entrega de muestra.</span></div>
<?php foreach ($groups as $key => $sections): ?>
    <section class="box">
        <div class="box__head"><h2><?= e(config("site.groups.$key")) ?></h2></div>
        <div style="display:grid;gap:14px">
            <?php foreach ($sections as $s): ?>
                <form class="form" method="post" action="/admin/secciones/<?= (int) $s['id'] ?>" style="padding-bottom:14px;border-bottom:1px solid var(--linea)">
                    <?= csrf_field() ?>
                    <div class="form-row">
                        <div class="field"><label>Nombre</label><input class="input" name="name" value="<?= e($s['name']) ?>"></div>
                        <div class="field"><label>Etiqueta</label><input class="input" name="kicker" value="<?= e($s['kicker']) ?>"></div>
                    </div>
                    <div class="field"><label>Introducción</label><input class="input" name="intro" value="<?= e($s['intro']) ?>"></div>
                    <div class="actions">
                        <?php foreach ($tiers as $t => $tier): ?>
                            <label class="check"><input type="checkbox" name="tiers[]" value="<?= e($t) ?>" <?= in_array($t, $s['tiers'], true) ? 'checked' : '' ?>> <?= e($tier['name']) ?></label>
                        <?php endforeach; ?>
                        <button class="btn btn--sm" type="submit">Guardar</button>
                        <a class="small" href="/admin/contenidos?seccion=<?= (int) $s['id'] ?>">Ver entregas →</a>
                    </div>
                </form>
            <?php endforeach; ?>
        </div>
    </section>
<?php endforeach; ?>
