<section class="band band--white">
    <div class="container">
        <div class="band__head"><span class="kicker">Lo que dicen</span><h2>Personas que ya transitan con Silvina</h2></div>
        <div class="grid grid--3" style="align-items:start">
            <?php foreach (config('site.testimonials') as [$quote, $who]): ?>
                <figure class="quote-card" style="margin:0">
                    <span class="quote-card__mark" aria-hidden="true">“</span>
                    <blockquote><?= e($quote) ?></blockquote>
                    <cite><?= e($who) ?></cite>
                </figure>
            <?php endforeach; ?>
        </div>
    </div>
</section>
