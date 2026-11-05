<?php if ($message = error($name)): ?>
    <span class="field__error" id="<?= e($name) ?>-error"><?= e($message) ?></span>
<?php endif; ?>
