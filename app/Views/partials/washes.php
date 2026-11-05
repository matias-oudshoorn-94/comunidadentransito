<?php
// Manchas de acuarela de fondo. $animated = true → se mueven muy despacio.
$animated = $animated ?? false;
?>
<span class="wash wash--light <?= $animated ? 'wash--drift' : '' ?>" aria-hidden="true"></span>
<span class="wash wash--sand <?= $animated ? 'wash--drift-2' : '' ?>" aria-hidden="true"></span>
<?php if ($animated): ?>
    <span class="wash wash--sea wash--drift" style="animation-delay:-12s" aria-hidden="true"></span>
<?php endif; ?>
