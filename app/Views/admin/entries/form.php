<?php
/** @var array $entry @var array $sections */
use App\Models\Entry;

$isNew = empty($entry['id']);
$action = $isNew ? '/admin/contenidos' : '/admin/contenidos/' . (int) $entry['id'];
$dt = fn (?string $v) => $v ? date('Y-m-d\TH:i', strtotime($v)) : '';
?>
<p><a href="/admin/contenidos">← Volver a contenidos</a></p>
<div class="cols cols--wide">
    <form class="box form" method="post" action="<?= $action ?>" novalidate id="entry-form">
        <?= csrf_field() ?>
        <div class="field <?= error('title') ? 'has-error' : '' ?>"><label for="title">Título</label><input class="input" id="title" name="title" value="<?= e(old('title', $entry['title'] ?? '')) ?>" required><?= partial('partials/field-error', ['name' => 'title']) ?></div>
        <div class="form-row">
            <div class="field"><label for="section_id">Sección</label>
                <select class="select" id="section_id" name="section_id">
                    <?php foreach ($sections as $s): ?><option value="<?= (int) $s['id'] ?>" <?= (int) old('section_id', (string) $entry['section_id']) === (int) $s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="field"><label for="kind">Tipo</label>
                <select class="select" id="kind" name="kind">
                    <?php foreach (Entry::KINDS as $k => $label): ?><option value="<?= $k ?>" <?= old('kind', $entry['kind']) === $k ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="field"><label for="kicker">Antetítulo</label><input class="input" id="kicker" name="kicker" value="<?= e(old('kicker', $entry['kicker'] ?? '')) ?>" placeholder="PODCAST · CHIRIMBITAS · EPISODIO 1"></div>
            <div class="field"><label for="meta">Línea de autor</label><input class="input" id="meta" name="meta" value="<?= e(old('meta', $entry['meta'] ?? '')) ?>" placeholder="Silvina Oudshoorn · 6 minutos de lectura"></div>
        </div>
        <div class="form-row">
            <div class="field"><label for="hand_note">Nota manuscrita</label><input class="input" id="hand_note" name="hand_note" value="<?= e(old('hand_note', $entry['hand_note'] ?? '')) ?>"></div>
            <div class="field"><label for="category">Categoría</label><input class="input" id="category" name="category" value="<?= e(old('category', $entry['category'] ?? '')) ?>" placeholder="autocuidados, encuentro, video…"><span class="hint">Para filtros y bloques de la sección.</span></div>
        </div>
        <div class="form-row">
            <div class="field"><label for="image">Imagen</label><input class="input" id="image" name="image" value="<?= e(old('image', $entry['image'] ?? '')) ?>" placeholder="fotos/archivo.jpg"><span class="hint">Ruta dentro de public/assets/img.</span></div>
            <div class="field"><label for="label">Texto de la tarjeta sin foto</label><input class="input" id="label" name="label" value="<?= e(old('label', $entry['label'] ?? '')) ?>" placeholder="carta de octubre"></div>
        </div>
        <div class="field"><label for="image_caption">Epígrafe de la imagen</label><input class="input" id="image_caption" name="image_caption" value="<?= e(old('image_caption', $entry['image_caption'] ?? '')) ?>"></div>
        <div class="field"><label for="excerpt">Adelanto</label><input class="input" id="excerpt" name="excerpt" maxlength="400" value="<?= e(old('excerpt', $entry['excerpt'] ?? '')) ?>"><span class="hint">Se muestra en la tarjeta.</span></div>
        <div class="field"><label for="body">Texto</label><textarea class="textarea" id="body" name="body" style="min-height:320px"><?= e(old('body', $entry['body'] ?? '')) ?></textarea><span class="hint">Dejá una línea en blanco entre párrafos. Imagen: <code>![epígrafe](fotos/archivo.jpg)</code> · Cita: <code>&gt; texto</code> y abajo <code>— Autora</code> · Recuadro: <code>[para probar en casa]</code> y el texto abajo.</span></div>
        <div class="field <?= error('media_url') ? 'has-error' : '' ?>"><label for="media_url">Archivo o link</label><input class="input" id="media_url" name="media_url" value="<?= e(old('media_url', $entry['media_url'] ?? '')) ?>" placeholder="https://… (PDF, audio, YouTube o Vimeo)"><?= partial('partials/field-error', ['name' => 'media_url']) ?></div>
    </form>

    <div style="display:grid;gap:24px">
        <div class="box form">
            <h2 style="font-size:1.15rem;margin:0">Publicación</h2>
            <div class="field"><label for="published_at">Fecha de publicación</label><input class="input" id="published_at" name="published_at" type="datetime-local" form="entry-form" value="<?= e($dt(old('published_at', $entry['published_at'] ?? ''))) ?>"></div>
            <div class="field"><label for="event_at">Fecha del encuentro</label><input class="input" id="event_at" name="event_at" type="datetime-local" form="entry-form" value="<?= e($dt(old('event_at', $entry['event_at'] ?? ''))) ?>"><span class="hint">Solo para encuentros (habilita “Agendar”).</span></div>
            <label class="check"><input type="checkbox" name="is_published" value="1" form="entry-form" <?= ($entry['is_published'] ?? 1) ? 'checked' : '' ?>> Publicada</label>
            <label class="check"><input type="checkbox" name="is_preview" value="1" form="entry-form" <?= ($entry['is_preview'] ?? 0) ? 'checked' : '' ?>> Muestra pública (“Así lo ve un miembro”)</label>
            <button class="btn" type="submit" form="entry-form"><?= $isNew ? 'Crear entrega' : 'Guardar cambios' ?></button>
        </div>
        <?php if (!$isNew): ?>
            <form class="box" method="post" action="/admin/contenidos/<?= (int) $entry['id'] ?>/borrar" data-confirm="¿Borrar esta entrega? No se puede deshacer.">
                <?= csrf_field() ?>
                <button class="btn btn--ghost btn--sm" type="submit">Borrar entrega</button>
            </form>
        <?php endif; ?>
    </div>
</div>
