<?php /** @var array $faq @var int|null $open */ ?>
<div class="faq">
    <?php foreach ($faq as $i => $item): $id = 'faq-' . substr(md5($item['q']), 0, 6); $isOpen = isset($open) && $open === $i; ?>
        <div class="faq__item">
            <button class="faq__q" type="button" aria-expanded="<?= $isOpen ? 'true' : 'false' ?>" aria-controls="<?= $id ?>" data-accordion>
                <span><?= e($item['q']) ?></span><span class="faq__sign" aria-hidden="true"></span>
            </button>
            <div class="faq__a <?= $isOpen ? 'is-open' : '' ?>" id="<?= $id ?>" role="region"><div><p><?= e($item['a']) ?></p></div></div>
        </div>
    <?php endforeach; ?>
</div>
