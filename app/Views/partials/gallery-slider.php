<?php
/**
 * Slider con la galería de una entrega.
 * @var array $images [['path' => …, 'caption' => …], …] @var string|null $title
 */
$images = array_values(array_filter($images, fn (array $i) => img_url($i['path']) !== null));
if ($images === []) {
    return;
}
$total = count($images);
$id = 'galeria-' . substr(md5(json_encode(array_column($images, 'path'))), 0, 6);
?>
<section class="slider" id="fotos" aria-roledescription="carrusel" aria-label="<?= e($title ?: 'Galería de imágenes') ?>" data-slider>
    <?php if (!empty($title)): ?><span class="hand slider__title"><?= e($title) ?></span><?php endif; ?>
    <div class="slider__viewport">
        <ul class="slider__track" id="<?= $id ?>" data-slider-track tabindex="0">
            <?php foreach ($images as $i => $img): ?>
                <li class="slider__slide" aria-roledescription="imagen" aria-label="<?= $i + 1 ?> de <?= $total ?>">
                    <figure>
                        <?= img($img['path'], $img['caption'] ?: 'Imagen ' . ($i + 1) . ' de ' . $total, $i === 0 ? '' : 'loading="lazy"') ?>
                        <?php if ($img['caption']): ?><figcaption><?= e($img['caption']) ?></figcaption><?php endif; ?>
                    </figure>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php if ($total > 1): ?>
            <button class="slider__arrow slider__arrow--prev" type="button" aria-controls="<?= $id ?>" aria-label="Imagen anterior" data-slider-prev><?= icon('back') ?></button>
            <button class="slider__arrow slider__arrow--next" type="button" aria-controls="<?= $id ?>" aria-label="Imagen siguiente" data-slider-next><?= icon('arrow') ?></button>
        <?php endif; ?>
    </div>
    <?php if ($total > 1): ?>
        <div class="slider__footer">
            <div class="slider__dots" role="group" aria-label="Elegir imagen">
                <?php for ($i = 0; $i < $total; $i++): ?>
                    <button type="button" aria-label="Ver imagen <?= $i + 1 ?>" <?= $i === 0 ? 'aria-current="true"' : '' ?> data-slider-dot="<?= $i ?>"></button>
                <?php endfor; ?>
            </div>
            <span class="slider__count" aria-live="polite" data-slider-count>1 / <?= $total ?></span>
        </div>
    <?php endif; ?>
</section>
