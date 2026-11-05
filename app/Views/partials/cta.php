<?php
/** Franja final. @var string $hand @var string $title @var string $button @var string|null $href @var string|null $bg @var string|null $note @var bool|null $whatsapp */
$href = $href ?? '/membresias';
?>
<section class="band band--<?= e($bg ?? 'sand') ?>">
    <div class="container cta">
        <?php if (!empty($hand)): ?><span class="hand"><?= e($hand) ?></span><?php endif; ?>
        <h2><?= e($title) ?></h2>
        <?php if (!empty($note)): ?><p><?= e($note) ?></p><?php endif; ?>
        <div class="actions">
            <a class="btn" href="<?= e($href) ?>"><?= e($button) ?></a>
            <?php if (!empty($whatsapp)): ?><a class="link-arrow" href="<?= e(whatsapp()) ?>" target="_blank" rel="noopener">¿Dudas? Escribime por WhatsApp</a><?php endif; ?>
            <?php if (!empty($extra)): ?><?= $extra ?><?php endif; ?>
        </div>
    </div>
</section>
