<?php /** @var array $faq */ ?>
<div class="faq">
    <?php foreach ($faq as $i => $item): $id = 'faq-' . $i; ?>
        <div class="faq__item">
            <button class="faq__q" type="button" aria-expanded="false" aria-controls="<?= $id ?>" data-accordion>
                <span><?= e($item['q']) ?></span>
                <span class="faq__sign" aria-hidden="true"></span>
            </button>
            <div class="faq__a" id="<?= $id ?>" role="region">
                <div><p><?= e($item['a']) ?></p></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
