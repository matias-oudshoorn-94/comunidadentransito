<?php /** A4 · Libros, con ficha en ventana  @var array $books */ ?>
<section class="page-hero">
    <?= partial('partials/washes') ?>
    <div class="container container--narrow center">
        <span class="kicker">Libros</span>
        <h1>Lo que fui escribiendo</h1>
        <p class="lead">Tocá un libro para ver su ficha.</p>
    </div>
</section>

<section class="section section--tight" style="padding-top:0">
    <div class="container">
        <div class="grid grid--3">
            <?php foreach ($books as $book): ?>
                <button class="card book" type="button" data-open-dialog="libro-<?= (int) $book['id'] ?>">
                    <div class="book__cover">
                        <span class="wash wash--light" aria-hidden="true"></span>
                        <?php if ($book['cover'] && asset_exists('img/' . $book['cover'])): ?>
                            <img src="<?= asset('img/' . $book['cover']) ?>" alt="">
                        <?php else: ?>
                            <strong><?= e($book['title']) ?></strong>
                        <?php endif; ?>
                    </div>
                    <div class="card__body">
                        <span class="kicker" style="margin:0"><?= e($book['subtitle']) ?> · <?= e($book['year']) ?></span>
                        <h3><?= e($book['title']) ?></h3>
                        <span class="card__foot">Ver ficha <?= icon('arrow') ?></span>
                    </div>
                </button>

                <dialog class="modal" id="libro-<?= (int) $book['id'] ?>" aria-labelledby="libro-<?= (int) $book['id'] ?>-titulo">
                    <button class="modal__close" type="button" aria-label="Cerrar" data-close-dialog><?= icon('close') ?></button>
                    <div class="modal__body book-sheet">
                        <div class="book-sheet__cover">
                            <div class="book__cover">
                                <?php if ($book['cover'] && asset_exists('img/' . $book['cover'])): ?>
                                    <img src="<?= asset('img/' . $book['cover']) ?>" alt="Tapa de <?= e($book['title']) ?>">
                                <?php else: ?>
                                    <strong><?= e($book['title']) ?></strong>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div>
                            <span class="kicker">Ficha del libro</span>
                            <h2 id="libro-<?= (int) $book['id'] ?>-titulo"><?= e($book['title']) ?></h2>
                            <dl>
                                <dt>Género</dt><dd><?= e($book['subtitle']) ?></dd>
                                <dt>Año</dt><dd><?= e($book['year']) ?></dd>
                                <?php if ($book['publisher']): ?><dt>Editorial</dt><dd><?= e($book['publisher']) ?></dd><?php endif; ?>
                                <?php if ($book['pages']): ?><dt>Páginas</dt><dd><?= e($book['pages']) ?></dd><?php endif; ?>
                            </dl>
                            <?= paragraphs($book['synopsis']) ?>
                            <?php if ($book['buy_url']): ?>
                                <a class="btn" href="<?= e($book['buy_url']) ?>" target="_blank" rel="noopener">Conseguir el libro <?= icon('external') ?></a>
                            <?php else: ?>
                                <a class="btn btn--ghost" href="/contacto">Consultar por el libro</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </dialog>
            <?php endforeach; ?>
        </div>
    </div>
</section>
