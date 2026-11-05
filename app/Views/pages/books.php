<?php /** A4 · Libros  @var array $books */ ?>
<?= partial('partials/hero', [
    'kicker' => 'Libros', 'hand' => 'escritos con el corazón', 'title' => 'Los libros de Silvina',
    'text' => 'Siete libros sobre salud, arte y acompañamiento. Si sos parte de la Comunidad, tenés 25% de descuento en papel.',
    'image' => 'fotos/libros-playa.jpg', 'ratio' => '520 / 380',
]) ?>

<section class="band band--top0">
    <div class="container">
        <div class="books">
            <?php foreach ($books as $book): $id = 'libro-' . (int) $book['id']; ?>
                <article class="book">
                    <button class="book__cover" type="button" data-open-dialog="<?= $id ?>" aria-label="Ver la ficha de <?= e($book['title']) ?>">
                        <?php if ($book['is_new']): ?><span class="pill">Nuevo</span><?php endif; ?>
                        <?= img($book['cover'], 'Tapa de ' . $book['title'], '') ?>
                    </button>
                    <span class="kicker"><?= e($book['year']) ?></span>
                    <h3><?= e($book['title']) ?></h3>
                    <?php if ($book['subtitle']): ?><p><?= e($book['subtitle']) ?></p><?php endif; ?>
                    <p><?= e($book['publisher']) ?></p>
                    <?php if ($book['sold_out']): ?>
                        <p class="book__out">Agotado por ahora</p>
                        <a class="btn--link" href="<?= e(whatsapp('Hola Silvina, quería consultar por el libro ' . $book['title'])) ?>" target="_blank" rel="noopener">Consultar por WhatsApp →</a>
                    <?php else: ?>
                        <?php if ($book['price']): ?>
                            <p class="book__price"><?= money($book['price']) ?></p>
                            <span class="book__member"><?= money(round($book['price'] * .75)) ?> para miembros</span><br>
                        <?php endif; ?>
                        <button class="btn--link" type="button" data-open-dialog="<?= $id ?>">Ver libro →</button>
                    <?php endif; ?>
                </article>

                <dialog class="modal" id="<?= $id ?>" aria-labelledby="<?= $id ?>-t">
                    <button class="modal__close" type="button" aria-label="Cerrar" data-close-dialog><?= icon('close') ?></button>
                    <div class="modal__body book-sheet">
                        <div class="book-sheet__cover"><?= img($book['cover'], 'Tapa de ' . $book['title'], '') ?></div>
                        <div>
                            <span class="kicker">Ficha del libro</span>
                            <h2 id="<?= $id ?>-t"><?= e($book['title']) ?></h2>
                            <?php if ($book['subtitle']): ?><p class="muted"><?= e($book['subtitle']) ?></p><?php endif; ?>
                            <dl>
                                <dt>Año</dt><dd><?= e($book['year']) ?></dd>
                                <dt>Editorial</dt><dd><?= e($book['publisher']) ?></dd>
                                <?php if ($book['price'] && !$book['sold_out']): ?><dt>Precio</dt><dd><?= money($book['price']) ?> · <?= money(round($book['price'] * .75)) ?> para miembros</dd><?php endif; ?>
                            </dl>
                            <?= paragraphs($book['synopsis']) ?>
                            <a class="btn" href="<?= e($book['buy_url'] ?: whatsapp('Hola Silvina, quiero el libro ' . $book['title'])) ?>" target="_blank" rel="noopener"><?= $book['sold_out'] ? 'Consultar por WhatsApp' : 'Lo quiero' ?></a>
                        </div>
                    </div>
                </dialog>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?= partial('partials/cta', ['hand' => 'un regalo para leer sin apuro', 'title' => 'Cada mes, un capítulo de regalo para los miembros', 'button' => 'Ver membresías', 'bg' => 'sky']) ?>
