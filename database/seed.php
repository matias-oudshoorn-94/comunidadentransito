<?php

declare(strict_types=1);

use App\Core\Database;
use App\Models\Plan;
use App\Models\Section;
use App\Models\User;
use App\Services\MembershipService;

/**
 * Datos base, con los textos de Figma. Los que dicen "a confirmar" los completa Silvina desde el admin.
 */
function seed_base(): void
{
    // Las cuentas de administración no se cargan acá: se crean con `php bin/create-admin.php`
    // o desde /admin/ingresar mientras no exista ninguna.

    // Precios de Figma (mensual). Anual = 10 meses ("2 meses de regalo"). Semestral y dólares: provisorios.
    $plans = [
        ['orilla-mensual',      'Orilla mensual',      'orilla',    'mensual',   1,  19999,  20,  'Para asomarte y probar.', 1],
        ['orilla-semestral',    'Orilla semestral',    'orilla',    'semestral', 6,  107990, 108, 'Para asomarte y probar.', 2],
        ['orilla-anual',        'Orilla anual',        'orilla',    'anual',     12, 199990, 200, 'Para asomarte y probar.', 3],
        ['inmersion-mensual',   'Inmersión mensual',   'inmersion', 'mensual',   1,  39990,  40,  'Toda la Comunidad, sin límites.', 4],
        ['inmersion-semestral', 'Inmersión semestral', 'inmersion', 'semestral', 6,  215940, 216, 'Toda la Comunidad, sin límites.', 5],
        ['inmersion-anual',     'Inmersión anual',     'inmersion', 'anual',     12, 399900, 400, 'Toda la Comunidad, sin límites.', 6],
    ];
    foreach ($plans as [$slug, $name, $tier, $period, $months, $ars, $usd, $description, $sort]) {
        if (!Plan::findBy('slug', $slug)) {
            Plan::create(compact('slug', 'name', 'tier', 'period', 'months', 'description', 'sort') + ['price_ars' => $ars, 'price_usd' => $usd]);
        }
    }

    // Orilla: Carta Slow Mood, Webinars (encuentros), Cafetería y algunos contenidos. Inmersión: todo.
    $both = ['orilla', 'inmersion'];
    $deep = ['inmersion'];
    $sections = [
        ['pispeando-arte', 'Pispeando Arte', 'crear', 'CREAR', 'eye', $deep,
            'Los cuadros de Silvina y, cada mes, una artista invitada. Para mirar despacio, como en una muestra.'],
        ['tecniquitas', 'Tecniquitas', 'crear', 'CREAR', 'pen', $both,
            'Diferentes técnicas y propuestas de actividades breves para que puedas experimentar en tus tiempos y con tus ganas: técnicas plásticas, corporales y de autoexpresión.'],
        ['runas-de-letras', 'Runas de Letras', 'crear', 'RUNAS DE LETRAS · LA CÁPSULA DEL ESCRITOR', 'sparkle', $both,
            'Un mazo de naipes para inspirar tu escritura. Sacá una o más por día y dejá que te hablen: las runas funcionan con la intuición y muestran lo que ya sabés, pero necesitás escuchar.'],
        ['materiales-para-leer', 'Materiales para leer', 'leer', 'LEER', 'book', $deep,
            'Para ir armando una biblioteca o reservorio sobre autocuidados, espiritualidad, cuidados paliativos, teorías y evidencia, artículos interesantes.'],
        ['capituleando', 'Capituleando', 'leer', 'LEER', 'layers', $deep,
            'Cada mes, un capítulo de uno de los libros de Silvina, para leer a tu ritmo.'],
        ['materiales-para-escuchar', 'Materiales para escuchar', 'leer', 'ESCUCHAR', 'headphones', $deep,
            'Chirimbitas, el ciclo de podcast de Silvina: pequeñas historias para el oído, con el objetivo de conectar y empatizar. Chispas de inspiración.'],
        ['materiales-para-ver', 'Materiales para ver', 'leer', 'VER', 'play', $both,
            'Un encuentro por Zoom cada mes, en vivo o grabado, sobre diferentes temáticas motivadoras. Y una selección de videos de otros autores que a Silvina le resultaron interesantes.'],
        ['cafeteria-en-transito', 'Cafetería en tránsito', 'encontrarnos', 'ENCONTRARNOS', 'cup', $both,
            'Tenemos un lugarcito hermoso al que vas a poder acceder cada vez que quieras, al alcance de tu mano: la Cafetería en tránsito, ubicada en tu WhatsApp.'],
        ['slow-mood', 'Slow Mood', 'encontrarnos', 'ENCONTRARNOS', 'mail', $both,
            'Una carta escrita por Silvina, cada mes, para leer con calma.'],
        ['lugares', 'Lugares', 'encontrarnos', 'ENCONTRARNOS', 'pin', $deep,
            'Viajar es un tránsito que va mucho más allá de la geografía cercana o lejana que podamos recorrer. Relatos de viajes, con lugares y descubrimientos a partir de la emoción.'],
        ['dejando-huellas', 'Dejando Huellas', 'encontrarnos', 'ENCONTRARNOS', 'footprint', $deep,
            'Ejemplos de personas que con sus hechos han dejado huella, actuales o históricas, en un relato coloquial y cercano. Cada mes agrego un relato de Huellas: arte, pasión, viajes, salud, desafíos, aventura, animarse a…'],
    ];
    foreach ($sections as $sort => [$slug, $name, $group, $kicker, $icon, $tiers, $intro]) {
        if (Section::findBy('slug', $slug)) {
            continue;
        }
        $id = Section::create([
            'slug' => $slug, 'name' => $name, 'group_key' => $group, 'kicker' => $kicker,
            'icon' => $icon, 'animated_header' => in_array($slug, ['slow-mood', 'lugares', 'dejando-huellas'], true) ? 1 : 0,
            'intro' => $intro, 'sort' => $sort,
        ]);
        Section::setTiers($id, $tiers);
        foreach (seed_entries_for($slug) as $i => $entry) {
            seed_entry($id, $entry, $i);
        }
    }

    seed_runes();
    seed_books();
}

/** Inserta una entrega. Fecha: el mes del antetítulo (octubre 2026 hacia atrás). */
function seed_entry(int $sectionId, array $e, int $i): void
{
    static $slugs = [];
    $base = slugify($e['title']);
    $slug = $base;
    $n = 2;
    while (isset($slugs[$sectionId][$slug])) {
        $slug = $base . '-' . $n++;
    }
    $slugs[$sectionId][$slug] = true;

    $published = $e['published_at'] ?? date('Y-m-d 10:00:00', strtotime("2026-10-01 -{$i} months"));
    Database::insert('entries', [
        'section_id'    => $sectionId,
        'slug'          => $slug,
        'title'         => $e['title'],
        'kind'          => $e['kind'] ?? 'lectura',
        'kicker'        => $e['kicker'] ?? null,
        'meta'          => $e['meta'] ?? null,
        'hand_note'     => $e['hand_note'] ?? null,
        'category'      => $e['category'] ?? null,
        'label'         => $e['label'] ?? null,
        'image'         => $e['image'] ?? null,
        'image_caption' => $e['image_caption'] ?? null,
        'gallery_title' => $e['gallery_title'] ?? null,
        'excerpt'       => $e['excerpt'] ?? null,
        'body'          => $e['body'] ?? null,
        'media_url'     => $e['media_url'] ?? null,
        'event_at'      => $e['event_at'] ?? null,
        'is_preview'    => (int) ($e['preview'] ?? 0),
        'is_published'  => 1,
        'published_at'  => $published,
        'created_at'    => $published,
        'updated_at'    => $published,
    ]);
    $entryId = (int) Database::pdo()->lastInsertId();
    foreach ($e['gallery'] ?? [] as $path) {
        \App\Models\EntryImage::add($entryId, $path);
    }
}

function seed_entries_for(string $slug): array
{
    $tbc = 'Título a confirmar con Silvina';
    $leer = "Porque la experiencia es fáctica, es algo que sucede, real; con la práctica, la reiteración, logramos domarla y domesticarla, organizando su ritmo, determinando sus pasos, un ordenamiento o secuencia; un conocimiento mutuo entre el que lo hace y esa actividad en particular.\n\n"
        . "Sencillamente, pasa de ser una experiencia desbocada a constituir una técnica consumada, con melodía y armonía en sus pasos.\n\n"
        . "Y cuando la técnica se incorpora (ya no es sólo una práctica de la experimentación inicial), cuando se vivencia y se afianza, es el instante en el que se convierte en destreza, la cual con el tiempo, nos libera.";

    return match ($slug) {
        'pispeando-arte' => [
            [
                'title' => 'La artista del mes: Dolores Mendieta', 'kicker' => 'PISPEANDO ARTE · OCTUBRE 2026',
                'meta' => 'por Silvina Oudshoorn · 6 min de lectura', 'hand_note' => 'Artista invitada',
                'image' => 'fotos/magnolia.jpg', 'image_caption' => 'El atelier de Dolores en San Javier, Córdoba',
                'excerpt' => 'Su atelier en San Javier, Córdoba. Un recorrido por su espacio de trabajo, sus materiales y lo que la inspira.',
                'body' => "Conocí su obra en un viaje con amigas a San Javier, Córdoba, donde visitamos su atelier. Entre telas, frascos con pinceles y una ventana que da a las sierras, Dolores nos mostró cómo trabaja.\n\n"
                    . "Dolores nace en Buenos Aires en 1977. Es licenciada en Artes Visuales y trabaja con acuarela y tinta sobre papel. Sus obras hablan del paisaje y de lo que queda cuando una se detiene a mirar.\n\n"
                    . "![Obra de la artista](fotos/mesa-acuarelas.jpg)\n![Obra de la artista](fotos/runa-dibujo.jpg)\n\n"
                    . "> Pintar es una forma de quedarse un rato más en un lugar.\n— Dolores Mendieta\n\n"
                    . "Texto de la entrega a cargo de Silvina: lo que le provocó su obra, una pregunta para quien lee y una invitación a probar algo parecido en casa.\n\n"
                    . "[para probar en casa]\nElegí una ventana de tu casa y pintá lo que ves en 15 minutos, sin borrar.",
            ],
            ['title' => 'Acuarelas del mar', 'kicker' => 'PISPEANDO ARTE · SEPTIEMBRE 2026', 'image' => 'fotos/mesa-acuarelas.jpg',
                'excerpt' => 'Cuadros de Silvina sobre Mar del Plata.', 'meta' => 'por Silvina Oudshoorn', 'body' => 'Texto a confirmar con Silvina.'],
            ['title' => 'Entrega anterior', 'kicker' => 'PISPEANDO ARTE · AGOSTO 2026', 'image' => 'fotos/runa-dibujo.jpg',
                'excerpt' => 'Título a confirmar.', 'meta' => 'por Silvina Oudshoorn', 'body' => 'Texto a confirmar con Silvina.'],
        ],
        'tecniquitas' => [
            ['title' => 'Tu color', 'kicker' => 'OCTUBRE 2026 · LÁMINA', 'image' => 'fotos/cafe-taza.jpg',
                'excerpt' => 'Elegí un color que te guste y animate a completar sus características: qué figura sería, qué sabor tendría, con qué emoción lo asociás… y escribile un poema de cuatro líneas.',
                'meta' => 'Una Tecniquita de Silvina · lámina para imprimir',
                'body' => "Elegí un color que te guste y animate a completar sus características: qué figura sería, qué sabor tendría, con qué emoción lo asociás.\n\nDespués, escribile un poema de cuatro líneas."],
        ],
        'runas-de-letras' => [],
        'materiales-para-leer' => [
            ['title' => $tbc, 'kicker' => 'ARTÍCULO · AUTOCUIDADOS', 'category' => 'autocuidados', 'label' => 'autocuidados', 'preview' => 1,
                'excerpt' => 'Autor o fuente · a confirmar', 'meta' => 'Autor o fuente · a confirmar · 3 minutos de lectura',
                'hand_note' => 'texto de ejemplo: acá va la lectura que elija Silvina', 'body' => $leer, 'published_at' => '2026-10-01 10:00:00'],
            ['title' => $tbc, 'kicker' => 'LECTURA · ESPIRITUALIDAD', 'category' => 'espiritualidad', 'label' => 'espiritualidad',
                'excerpt' => 'Autor o fuente · a confirmar', 'meta' => 'Autor o fuente · a confirmar', 'body' => $leer, 'published_at' => '2026-09-20 10:00:00'],
            ['title' => $tbc, 'kicker' => 'ARTÍCULO · TEORÍAS Y EVIDENCIA', 'category' => 'teorias-y-evidencia', 'label' => 'teorías y evidencia',
                'excerpt' => 'Autor o fuente · a confirmar', 'meta' => 'Autor o fuente · a confirmar', 'body' => $leer, 'published_at' => '2026-09-10 10:00:00'],
        ],
        'capituleando' => [
            ['title' => 'Habitante de la Vida', 'kicker' => 'OCTUBRE 2026 · ESTE MES', 'image' => 'libros/habitante-de-la-vida.jpg', 'preview' => 1,
                'excerpt' => 'Un capítulo del libro que completa la trilogía de En la Cornisa de la Vida.', 'meta' => 'Un capítulo de regalo · PDF para leer o imprimir',
                'body' => 'Un capítulo del libro que completa la trilogía de En la Cornisa de la Vida.'],
            ['title' => 'Apasionadamente', 'kicker' => 'SEPTIEMBRE 2026', 'image' => 'libros/apasionadamente.jpg',
                'excerpt' => 'Un capítulo de Apasionadamente · Un estilo de vida.', 'body' => 'Un capítulo de Apasionadamente.'],
            ['title' => 'El Secreto de la Nuez', 'kicker' => 'AGOSTO 2026', 'image' => 'libros/el-secreto-de-la-nuez.jpg',
                'excerpt' => 'Un capítulo de El Secreto de la Nuez.', 'body' => 'Un capítulo de El Secreto de la Nuez.'],
        ],
        'materiales-para-escuchar' => array_map(fn (int $n) => [
            'title' => $tbc, 'kind' => 'episodio', 'kicker' => "PODCAST · CHIRIMBITAS · EPISODIO {$n}", 'label' => 'chirimbitas',
            'preview' => $n === 1 ? 1 : 0, 'excerpt' => 'Duración a confirmar', 'meta' => 'Silvina Oudshoorn · duración a confirmar',
            'hand_note' => 'ejemplo: acá va el episodio que suba Silvina',
            'body' => 'Chirimbitas: pequeñas historias para el oído con el objetivo fundamental de conectar y empatizar, chispas de inspiración.',
        ], [1, 2, 3]),
        'materiales-para-ver' => [
            ...array_map(fn (array $m) => [
                'title' => 'Tema a confirmar con Silvina', 'kind' => 'grabacion', 'category' => 'encuentro', 'label' => 'encuentro',
                'kicker' => "ZOOM · {$m[0]} 2026 · GRABADO", 'preview' => $m[1], 'excerpt' => 'Grabado · duración a confirmar',
                'meta' => 'Encuentro mensual por Zoom · duración a confirmar', 'hand_note' => 'ejemplo: acá va la grabación del encuentro',
                'body' => 'Te invito a participar de un encuentro por zoom mensual, sincrónico (o queda grabado) en el que iremos abordando diferentes temáticas motivadoras.',
                'published_at' => $m[2],
            ], [['OCTUBRE', 1, '2026-10-01 10:00:00'], ['SEPTIEMBRE', 0, '2026-09-01 10:00:00'], ['AGOSTO', 0, '2026-08-01 10:00:00']]),
            ...array_map(fn (array $v) => [
                'title' => $v[0], 'kind' => 'grabacion', 'category' => 'video', 'label' => 'video recomendado',
                'kicker' => 'VIDEO · ' . upper($v[1]), 'excerpt' => $v[1], 'meta' => $v[1], 'body' => 'Un video recomendado por Silvina.',
                'published_at' => $v[2],
            ], [
                ['La felicidad es una elección: así puedes empezar hoy', 'Mario Alonso Puig', '2026-10-05 10:00:00'],
                ['Felicidad y propósito de vida', 'Pablo Gómez Psiquiatra', '2026-09-05 10:00:00'],
                ['Gestión del estrés y el aburrimiento', 'Marian Rojas Estapé', '2026-08-05 10:00:00'],
                ['Hablando de TO #29 · La muerte como parte de la vida', 'Silvina Oudshoorn', '2026-07-05 10:00:00'],
            ]),
        ],
        'cafeteria-en-transito' => [
            ['title' => 'La Cafetería, en tu WhatsApp', 'kicker' => 'GRUPO DE WHATSAPP', 'image' => 'fotos/cafe-taza.jpg', 'preview' => 1,
                'excerpt' => 'Charlas e intercambio cercano y permanente.', 'meta' => 'El grupo de WhatsApp de la Comunidad',
                'body' => 'Ni bien ingreses a la Comunidad en Tránsito, te sumamos al grupo de WhatsApp para favorecer el contacto e intercambio cercano y permanente.'],
            ['title' => 'Te sumamos al grupo', 'kicker' => 'AL SUMARTE', 'image' => 'fotos/collage-cafes.jpg',
                'excerpt' => 'Ni bien ingresás a la Comunidad, te sumamos al grupo de WhatsApp.', 'body' => 'Ni bien ingresás a la Comunidad, te sumamos al grupo de WhatsApp.'],
            ['title' => 'Próximo cafecito', 'kind' => 'encuentro', 'kicker' => 'EN WHATSAPP', 'image' => 'fotos/comedor.jpg',
                'excerpt' => 'Tema a confirmar con Silvina.', 'body' => 'Tema a confirmar con Silvina.'],
        ],
        'slow-mood' => array_map(fn (array $m) => [
            'title' => "Carta de {$m[0]}", 'kind' => 'carta', 'kicker' => 'SLOW MOOD · ' . upper($m[0]) . ' 2026', 'label' => "carta de {$m[0]}",
            'preview' => $m[1], 'excerpt' => 'Tema a confirmar con Silvina.', 'meta' => 'Una carta de Silvina · llega a tu casa y queda guardada acá',
            'hand_note' => 'texto de ejemplo: acá va la carta de cada mes',
            'body' => '¿Querés un cafecito? Tenemos un lugarcito hermoso al que vas a poder acceder cada vez que quieras, al alcance de tu mano.',
        ], [['octubre', 1], ['septiembre', 0], ['agosto', 0]]),
        'lugares' => [
            [
                'title' => 'Mainumbí, pradera naturalizada', 'kind' => 'relato', 'kicker' => 'LUGARES · ENTREGA DEL MES · OCTUBRE 2026',
                'image' => 'fotos/mainumbi-1.jpg', 'preview' => 1, 'meta' => 'Un relato de Silvina · 6 minutos de lectura',
                'excerpt' => 'Un campo de flores en la provincia de Buenos Aires.',
                'body' => "Hace unos años encontré buceando por internet un campo de flores en la provincia de Buenos Aires, que se llama Mainumbí. Originalmente fueron los campos de la familia Mulcahy, cuyo hermoso jardín estilo inglés se destacaba entre todos los otros. Sin embargo, decidieron darle a Mainumbí la oportunidad de volver a alojar a los polinizadores originales reinsertando lentamente la flora autóctona local. Así, el prolijo césped transmutó y dio paso a hermosas praderas naturalizadas plenas de vida; retornaron miles de aves silvestres e insectos… ¡y qué decirles de las mariposas!\n\n"
                    . "Cuando vi las fotos del lugar sentí la imperiosa necesidad de buscar la forma de poder conocerlo, recorrer, tomar mi tiempo, escribir, pintar y soñar en ese campo de flores. Muy decidida, armé un bolso pequeño con lo indispensable para tres o cuatro días y otro con varios libros, un cuaderno en blanco, lápices, pinceles, papel de pintura y acuarelas.\n\n"
                    . "![](fotos/papeles-pasto.jpg)\n\n"
                    . "Los invito a imaginar esas tardecitas calurosas bajo las frondosas copas de árboles antiguos. Pastizales ondulados por el viento y salpicados de pequeñas flores que brillan como joyas; senderos estrechos entre las matas y macizos de colores que conducen a nuevos paisajes; un arroyo que nutre el estanque del que Monet no se hubiera despegado sin antes pintar unas quinientas obras; un arco de rosas que enmarca la entrada a un huerto lleno de magia…\n\n"
                    . "Una mañana, después de tomar un desayuno casero, me fui a leer bajo una enorme magnolia. Estaba cargada de flores y su fragancia se hacía hasta empalagosa por su intensidad. No me podía concentrar y dejé que mi mente se fuera a vagabundear; en un instante me encontré afirmando en silencio “si alguna vez me vuelvo a casar, mi ramo va a ser de magnolias”.\n\n"
                    . "Los días que estuve en Mainumbí pasaron volando. Lo que sí puedo asegurar es que mi tránsito por el campo de flores fue una experiencia especial, que atesoraré toda la vida. Se trata de cerrar etapas, de cumplir sueños, de arriesgarse a que suceda.\n\n"
                    . "¿Se acuerdan lo que les conté sobre la magnolia? Bueno… tres años más tarde conocí al amor de mi vida ¡y nos casamos!",
                'gallery_title' => 'explorá las imágenes de este maravilloso lugar',
                'gallery' => ['fotos/mainumbi-1.jpg', 'fotos/mainumbi-2.jpg', 'fotos/mainumbi-3.jpg', 'fotos/mainumbi-4.jpg', 'fotos/mainumbi-5.jpg', 'fotos/flores-lilas.jpg'],
            ],
            ['title' => 'Lugar a confirmar con Silvina', 'kind' => 'relato', 'kicker' => 'NOVIEMBRE 2026', 'label' => 'próximo lugar',
                'excerpt' => 'Muy pronto.', 'body' => 'Muy pronto.', 'published_at' => '2026-09-01 10:00:00'],
            ['title' => 'Lugar a confirmar con Silvina', 'kind' => 'relato', 'kicker' => 'DICIEMBRE 2026', 'label' => 'otro lugar',
                'excerpt' => 'Muy pronto.', 'body' => 'Muy pronto.', 'published_at' => '2026-08-01 10:00:00'],
        ],
        'dejando-huellas' => array_map(fn (array $m) => [
            'title' => 'Relato a confirmar con Silvina', 'kind' => 'relato', 'kicker' => 'DEJANDO HUELLAS · ' . upper($m[0]) . ' 2026',
            'label' => 'huellas', 'preview' => $m[1], 'excerpt' => $m[2], 'meta' => 'Un relato de Silvina · duración a confirmar',
            'hand_note' => 'texto de ejemplo: acá va el relato del mes',
            'body' => 'Esta sección presenta ejemplos de personas que con sus hechos han dejado huella, actuales o históricas, llevados en relato coloquial y cercano. Que remitan a las experiencias, que puedan ser replicadas, sentidas por ellos.',
        ], [['octubre', 1, 'Arte, pasión, viajes, salud, desafíos…'], ['septiembre', 0, 'Tema a confirmar.'], ['agosto', 0, 'Tema a confirmar.']]),
        default => [],
    };
}

function seed_runes(): void
{
    if ((int) Database::value('SELECT COUNT(*) FROM runes') > 0) {
        return;
    }
    // Consignas provisorias: el mazo definitivo lo escribe Silvina.
    $runes = [
        ['ᚠ', 'Fehu', 'Abundancia', 'Escribí sobre algo que tenés en abundancia y que casi nunca nombrás.'],
        ['ᚢ', 'Uruz', 'Fuerza', 'Contá un momento en que descubriste una fuerza que no sabías que tenías.'],
        ['ᚦ', 'Thurisaz', 'Umbral', 'Describí una puerta que todavía no te animás a abrir.'],
        ['ᚨ', 'Ansuz', 'Palabra', 'Elegí una palabra que te guste cómo suena y escribí diez líneas que giren a su alrededor.'],
        ['ᚱ', 'Raidho', 'Viaje', 'Escribí sobre un viaje corto: de tu casa a la esquina, mirando todo por primera vez.'],
        ['ᚲ', 'Kenaz', 'Luz', 'Contá dónde da la luz en tu casa a esta hora y qué ilumina.'],
        ['ᚷ', 'Gebo', 'Regalo', 'Escribí una carta de agradecimiento a alguien que no la espera.'],
        ['ᚹ', 'Wunjo', 'Alegría', 'Hacé una lista de alegrías pequeñas de esta semana.'],
        ['ᚺ', 'Hagalaz', 'Tormenta', 'Describí una tormenta que viste desde adentro, con todos los sentidos.'],
        ['ᚾ', 'Nauthiz', 'Necesidad', 'Escribí sobre lo que de verdad necesitás hoy, sin vueltas.'],
        ['ᛁ', 'Isa', 'Quietud', 'Quedate quieto o quieta cinco minutos y después escribí lo que escuchaste.'],
        ['ᛃ', 'Jera', 'Cosecha', 'Escribí sobre algo que sembraste hace tiempo y hoy está dando fruto.'],
        ['ᛇ', 'Eihwaz', 'Raíz', 'Contá una historia de tu familia que te gustaría que no se pierda.'],
        ['ᛈ', 'Perthro', 'Misterio', 'Empezá un texto con la frase: “Nadie sabía qué había en la caja”.'],
        ['ᛉ', 'Algiz', 'Refugio', 'Describí el lugar donde te sentís a salvo.'],
        ['ᛊ', 'Sowilo', 'Sol', 'Escribí un texto que transcurra en una sola tarde de sol.'],
        ['ᛏ', 'Tiwaz', 'Coraje', 'Contá algo que hiciste aunque tenías miedo.'],
        ['ᛒ', 'Berkano', 'Crecimiento', 'Escribí una carta a la persona que eras hace diez años.'],
        ['ᛖ', 'Ehwaz', 'Compañía', 'Escribí sobre alguien que te acompaña sin hacer ruido.'],
        ['ᛗ', 'Mannaz', 'Humanidad', 'Observá a una persona desconocida y contá su día imaginado.'],
        ['ᛚ', 'Laguz', 'Agua', 'Escribí un texto que tenga mar, río o lluvia en cada párrafo.'],
        ['ᛜ', 'Ingwaz', 'Semilla', 'Escribí el comienzo de algo que todavía no sabés cómo termina.'],
        ['ᛞ', 'Dagaz', 'Amanecer', 'Describí un amanecer que recuerdes, con hora y lugar.'],
        ['ᛟ', 'Othala', 'Hogar', 'Contá cómo olía la casa de tu infancia.'],
    ];
    foreach ($runes as [$symbol, $name, $meaning, $prompt]) {
        Database::insert('runes', compact('symbol', 'name', 'meaning', 'prompt') + ['created_at' => now(), 'updated_at' => now()]);
    }
}

function seed_books(): void
{
    if ((int) Database::value('SELECT COUNT(*) FROM books') > 0) {
        return;
    }
    $books = [
        ['El Secreto de la Nuez', 'Ayuditas para decir y acompañar', 2025, 'Ed. Silvina Oudshoorn · Mar del Plata', 15000, 1, 0],
        ['Habitante de la Vida', 'Y después de la cornisa ¿qué?', 2025, 'Ed. Silvina Oudshoorn · Mar del Plata', 30000, 1, 0],
        ['Apasionadamente', 'Un estilo de vida', 2022, 'Ed. Dunken · Buenos Aires', 40000, 0, 0],
        ['Las otras cornisas de la terapia ocupacional', null, 2018, 'Ed. M.B · Miramar', 40000, 0, 0],
        ['A tu lado en la Cornisa de la Vida', 'Interdisciplina y acompañamiento', 2016, 'Ed. Martín · Mar del Plata', 30000, 0, 0],
        ['En la Cornisa de la Vida', 'Intervenciones desde Terapia Ocupacional', 2015, 'Ed. Martín · Mar del Plata', null, 0, 1],
        ['El niño con cáncer, su familia y la comunidad', null, 2007, 'Ed. Martín · Mar del Plata', 30000, 0, 0],
    ];
    $covers = ['el-secreto-de-la-nuez', 'habitante-de-la-vida', 'apasionadamente', 'las-otras-cornisas', 'a-tu-lado-en-la-cornisa', 'en-la-cornisa-de-la-vida', 'el-nino-con-cancer'];
    foreach ($books as $i => [$title, $subtitle, $year, $publisher, $price, $new, $soldOut]) {
        Database::insert('books', [
            'slug' => slugify($title), 'title' => $title, 'subtitle' => $subtitle, 'year' => $year,
            'publisher' => $publisher, 'price' => $price, 'is_new' => $new, 'sold_out' => $soldOut,
            'cover' => 'libros/' . $covers[$i] . '.jpg',
            'synopsis' => 'Sinopsis a confirmar con Silvina.', 'buy_url' => null, 'sort' => $i,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}

/** Miembros, membresías y pagos de prueba para ver el admin y el calendario con datos. */
function seed_demo(): void
{
    $people = [
        ['Juana Pérez', 'inmersion-mensual', -24, 'mercadopago'],
        ['Lucía Fernández', 'inmersion-anual', -200, 'mercadopago'],
        ['Andrés Gómez', 'orilla-mensual', -25, 'mercadopago'],
        ['Valentina Ríos', 'inmersion-mensual', -10, 'paypal'],
        ['Sol Martínez', 'inmersion-semestral', -170, 'mercadopago'],
        ['Camila Sosa', 'orilla-mensual', -40, 'mercadopago'],
        ['Tomás Pereyra', 'inmersion-mensual', -3, 'mercadopago'],
        ['Inés Acosta', 'orilla-mensual', -18, 'paypal'],
    ];
    foreach ($people as [$name, $planSlug, $daysAgo, $gateway]) {
        $email = slugify($name) . '@ejemplo.com';
        if (User::findByEmail($email)) {
            continue;
        }
        $userId = User::create([
            'name' => $name, 'email' => $email,
            'password_hash' => password_hash('miembro123', PASSWORD_DEFAULT),
            'address' => 'Calle y número', 'city' => 'Mar del Plata', 'postal_code' => '7600',
            'created_at' => date('Y-m-d H:i:s', strtotime("{$daysAgo} days")),
        ]);
        $plan = Plan::findBy('slug', $planSlug);
        MembershipService::assignManual($userId, (int) $plan['id'], date('Y-m-d', strtotime("{$daysAgo} days")), true, $gateway, 'Dato de prueba');
    }
    echo "  Miembros de prueba: contraseña \"miembro123\" (por ejemplo juana-perez@ejemplo.com)\n";

    // Administración de prueba (solo desarrollo)
    if (!\App\Models\Admin::findByEmail('admin@ejemplo.com')) {
        \App\Models\Admin::register('Silvina (prueba)', 'admin@ejemplo.com', 'admin-de-prueba');
        echo "  Admin de prueba: admin@ejemplo.com / admin-de-prueba (en /admin/ingresar)\n";
    }
}
