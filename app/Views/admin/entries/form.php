<?php
/** @var array $entry @var array $sections */
use App\Models\Entry;

$isNew = empty($entry['id']);
$action = $isNew ? '/admin/contenidos' : '/admin/contenidos/' . (int) $entry['id'];
$dt = fn (?string $v) => $v ? date('Y-m-d\TH:i', strtotime($v)) : '';
$media = (string) ($entry['media_url'] ?? '');
$mediaIsFile = str_starts_with($media, '/uploads/');
$source = old('media_source', $mediaIsFile ? 'file' : 'link');
$ext = strtolower(pathinfo(parse_url($media, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
$limit = \App\Services\Uploader::serverLimit();
?>
<p><a href="/admin/contenidos">← Volver a contenidos</a></p>
<div class="cols cols--wide">
    <form class="box form" method="post" action="<?= $action ?>" enctype="multipart/form-data" novalidate id="entry-form">
        <?= csrf_field() ?>
        <?php if ($msg = error('files')): ?><div class="notice notice--alert"><?= icon('close') ?><span><?= e($msg) ?></span></div><?php endif; ?>
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
        <fieldset class="upload">
            <legend>Imagen</legend>
            <div class="upload__row">
                <div class="upload__preview" data-preview>
                    <?php if ($url = img_url($entry['image'] ?? null)): ?><img src="<?= e($url) ?>" alt=""><?php elseif ($thumb = youtube_thumb($entry['media_url'] ?? null)): ?><?= img($thumb, '', 'data-video-thumb') ?><?php else: ?><span>Sin imagen</span><?php endif; ?>
                </div>
                <div class="upload__controls">
                    <label class="btn btn--soft btn--sm upload__button"><?= icon('download') ?> Subir archivo
                        <input type="file" name="image_file" accept="image/jpeg,image/png,image/webp,image/gif" data-preview-input>
                    </label>
                    <span class="hint" data-file-name>JPG, PNG o WebP, hasta 15 MB. Mejor apaisada, de al menos 1200 px de ancho.</span>
                    <span class="hint">Si no subís una imagen y el video es de YouTube, se usa la miniatura del video.</span>
                    <?php if (!empty($entry['image'])): ?><label class="check"><input type="checkbox" name="image_remove" value="1"> Quitar la imagen</label><?php endif; ?>
                </div>
            </div>
            <div class="form-row">
                <div class="field"><label for="label">Texto de la tarjeta sin imagen</label><input class="input" id="label" name="label" value="<?= e(old('label', $entry['label'] ?? '')) ?>" placeholder="carta de octubre"></div>
                <div class="field"><label for="image_caption">Epígrafe</label><input class="input" id="image_caption" name="image_caption" value="<?= e(old('image_caption', $entry['image_caption'] ?? '')) ?>"></div>
            </div>
        </fieldset>
        <fieldset class="upload" data-gallery>
            <legend>Galería de imágenes</legend>
            <p class="hint" style="margin:0">Se muestran en un slider dentro de la entrega, aparte de la imagen principal. Podés elegir varias a la vez<?= ini_get('max_file_uploads') ? ' (hasta ' . (int) ini_get('max_file_uploads') . ' por vez)' : '' ?>.</p>
            <div class="field"><label for="gallery_title">Título del slider <span class="muted">(opcional)</span></label><input class="input" id="gallery_title" name="gallery_title" value="<?= e(old('gallery_title', $entry['gallery_title'] ?? '')) ?>" placeholder="explorá las imágenes de este lugar"></div>
            <ol class="gallery-admin" data-gallery-list>
                <?php foreach ($gallery ?? [] as $i => $g): ?>
                    <li class="gallery-admin__item" data-gallery-item>
                        <img src="<?= e(img_url($g['path']) ?? '') ?>" alt="">
                        <input type="hidden" name="gallery[<?= (int) $g['id'] ?>][sort]" value="<?= $i + 1 ?>" data-sort>
                        <input class="input" name="gallery[<?= (int) $g['id'] ?>][caption]" value="<?= e($g['caption']) ?>" placeholder="Epígrafe (opcional)" aria-label="Epígrafe">
                        <div class="gallery-admin__tools">
                            <button class="btn btn--ghost btn--xs" type="button" data-move="-1" aria-label="Mover antes">←</button>
                            <button class="btn btn--ghost btn--xs" type="button" data-move="1" aria-label="Mover después">→</button>
                            <label class="check small"><input type="checkbox" name="gallery[<?= (int) $g['id'] ?>][remove]" value="1" data-remove> Quitar</label>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ol>
            <div class="gallery-admin__new" data-gallery-new hidden></div>
            <div>
                <label class="btn btn--soft btn--sm upload__button"><?= icon('plus') ?> Agregar imágenes
                    <input type="file" name="gallery_files[]" accept="image/jpeg,image/png,image/webp,image/gif" multiple data-gallery-input>
                </label>
                <span class="hint" data-gallery-count>JPG, PNG o WebP, hasta 15 MB cada una. Los epígrafes de las nuevas se escriben después de guardar.</span>
            </div>
        </fieldset>
        <div class="field"><label for="excerpt">Adelanto</label><input class="input" id="excerpt" name="excerpt" maxlength="400" value="<?= e(old('excerpt', $entry['excerpt'] ?? '')) ?>"><span class="hint">Se muestra en la tarjeta.</span></div>
        <div class="field editor" data-editor="body" data-upload-url="/admin/subir-imagen">
            <div class="upload__label-row">
                <span class="label" id="body-label">Texto de la entrega</span>
                <button class="btn--link small" type="button" data-editor-mode>Editar como texto</button>
            </div>
            <div class="editor__blocks" data-editor-blocks aria-labelledby="body-label"></div>
            <textarea class="textarea editor__source" id="body" name="body" style="min-height:320px" aria-labelledby="body-label"><?= e(old('body', $entry['body'] ?? '')) ?></textarea>
            <span class="hint editor__hint">Usá “+” para sumar párrafos, imágenes, citas o el recuadro “para probar en casa”, y las flechas para cambiar el orden. En los párrafos: Enter arma un párrafo nuevo y Shift+Enter hace un salto de línea.</span>
        </div>
        <fieldset class="upload" data-media>
            <legend>Video, audio o PDF</legend>
            <div class="upload__tabs" role="radiogroup" aria-label="Origen">
                <label class="check"><input type="radio" name="media_source" value="link" <?= $source === 'link' ? 'checked' : '' ?>> Link (YouTube, Vimeo u otra dirección)</label>
                <label class="check"><input type="radio" name="media_source" value="file" <?= $source === 'file' ? 'checked' : '' ?>> Subir archivo</label>
            </div>
            <div class="field" data-media-panel="link" <?= $source === 'link' ? '' : 'hidden' ?>>
                <input class="input" id="media_url" name="media_url" value="<?= e(old('media_url', $mediaIsFile ? '' : $media)) ?>" placeholder="https://www.youtube.com/watch?v=…" aria-label="Link">
                <span class="hint">Videos: YouTube o Vimeo se ven dentro de la página. También sirve un link directo a un MP3 o PDF.</span>
            </div>
            <div data-media-panel="file" <?= $source === 'file' ? '' : 'hidden' ?>>
                <?php if ($mediaIsFile): ?>
                    <div class="upload__current">
                        <?php if (in_array($ext, ['mp3', 'm4a', 'aac', 'ogg', 'wav'], true)): ?>
                            <audio controls preload="none" src="<?= e($media) ?>"></audio>
                        <?php elseif (in_array($ext, ['mp4', 'webm', 'mov'], true)): ?>
                            <video controls preload="metadata" src="<?= e($media) ?>"></video>
                        <?php else: ?>
                            <a href="<?= e($media) ?>" target="_blank" rel="noopener"><?= icon('doc') ?> Ver el PDF actual</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <label class="btn btn--soft btn--sm upload__button"><?= icon('download') ?> <?= $mediaIsFile ? 'Reemplazar archivo' : 'Subir archivo' ?>
                    <input type="file" name="media_file" accept="audio/*,video/mp4,video/webm,video/quicktime,application/pdf" data-file-input>
                </label>
                <span class="hint" data-file-name>Audio (MP3, M4A), video (MP4) o PDF. Máximo <?= e($limit) ?> por archivo. Para videos largos conviene YouTube.</span>
            </div>
            <?php if ($media !== ''): ?><label class="check"><input type="checkbox" name="media_remove" value="1"> Quitar el video, audio o PDF</label><?php endif; ?>
        </fieldset>
    </form>

    <div style="display:grid;gap:24px">
        <div class="box form">
            <h2 style="font-size:1.15rem;margin:0">Publicación</h2>
            <div class="field"><label for="published_at">Fecha de publicación</label><input class="input" id="published_at" name="published_at" type="datetime-local" form="entry-form" value="<?= e($dt(old('published_at', $entry['published_at'] ?? ''))) ?>"></div>
            <div class="field"><label for="event_at">Fecha del encuentro</label><input class="input" id="event_at" name="event_at" type="datetime-local" form="entry-form" value="<?= e($dt(old('event_at', $entry['event_at'] ?? ''))) ?>"><span class="hint">Solo para encuentros (habilita “Agendar”).</span></div>
            <label class="check"><input type="checkbox" name="is_published" value="1" form="entry-form" <?= ($entry['is_published'] ?? 1) ? 'checked' : '' ?>> Publicada</label>
            <label class="check"><input type="checkbox" name="is_preview" value="1" form="entry-form" <?= ($entry['is_preview'] ?? 0) ? 'checked' : '' ?>> Muestra pública (“Así lo ve un miembro”)</label>
            <button class="btn" type="submit" form="entry-form" data-submit-uploading="Subiendo archivos…"><?= $isNew ? 'Crear entrega' : 'Guardar cambios' ?></button>
        </div>
        <?php if (!$isNew): ?>
            <form class="box" method="post" action="/admin/contenidos/<?= (int) $entry['id'] ?>/borrar" data-confirm="¿Borrar esta entrega? No se puede deshacer.">
                <?= csrf_field() ?>
                <button class="btn btn--ghost btn--sm" type="submit">Borrar entrega</button>
            </form>
        <?php endif; ?>
    </div>
</div>
