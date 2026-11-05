<?php /** @var array $messages @var array $subscribers */ ?>
<div class="cols cols--wide">
    <section class="box">
        <div class="box__head"><h2>Mensajes de contacto</h2></div>
        <?php foreach ($messages as $m): ?>
            <article style="padding:16px 0;border-bottom:1px solid var(--linea);<?= $m['read_at'] ? 'opacity:.7' : '' ?>">
                <div class="box__head" style="margin-bottom:6px">
                    <div><strong><?= e($m['name']) ?></strong> · <a href="mailto:<?= e($m['email']) ?>"><?= e($m['email']) ?></a><span class="sub small muted"><?= fecha($m['created_at'], true) ?><?= $m['subject'] ? ' · ' . e($m['subject']) : '' ?></span></div>
                    <form method="post" action="/admin/mensajes/<?= (int) $m['id'] ?>/leido"><?= csrf_field() ?><button class="btn btn--ghost btn--xs" type="submit"><?= $m['read_at'] ? 'Marcar sin leer' : 'Marcar leído' ?></button></form>
                </div>
                <?= paragraphs($m['message']) ?>
            </article>
        <?php endforeach; ?>
        <?php if (!$messages): ?><p class="empty">No hay mensajes.</p><?php endif; ?>
    </section>
    <section class="box">
        <div class="box__head"><h2>Regalo de Tecniquitas</h2><span class="small muted"><?= count($subscribers) ?> emails</span></div>
        <table class="atable"><tbody>
            <?php foreach ($subscribers as $s): ?><tr><td><?= e($s['email']) ?></td><td class="num"><?= fecha_corta($s['created_at']) ?></td></tr><?php endforeach; ?>
            <?php if (!$subscribers): ?><tr><td class="empty">Todavía nadie pidió el regalo.</td></tr><?php endif; ?>
        </tbody></table>
    </section>
</div>
