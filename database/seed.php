<?php

declare(strict_types=1);

use App\Core\Database;
use App\Models\Plan;
use App\Models\Section;
use App\Models\User;
use App\Services\MembershipService;

/**
 * Datos base. Los precios, textos de secciones, consignas de runas y libros son
 * provisorios: hay que reemplazarlos por los definitivos (desde el admin o acá).
 */
function seed_base(): void
{
    // Cuenta admin
    $email = strtolower((string) env('ADMIN_EMAIL', 'admin@comunidadentransito.local'));
    if (!User::findByEmail($email)) {
        User::create([
            'name'          => (string) env('ADMIN_NAME', 'Silvina'),
            'email'         => $email,
            'password_hash' => password_hash((string) env('ADMIN_PASSWORD', 'cambiar-esta-clave'), PASSWORD_DEFAULT),
            'role'          => 'admin',
        ]);
        echo "  Admin: {$email}\n";
    }

    // Planes (precios provisorios)
    $plans = [
        ['orilla-mensual',      'Orilla mensual',      'orilla',    'mensual',   1,  9000,   9,   'Para asomarte a la comunidad: crear, escribir y leer.', 1],
        ['inmersion-mensual',   'Inmersión mensual',   'inmersion', 'mensual',   1,  18000,  18,  'Todo lo que pasa en la comunidad, mes a mes.', 2],
        ['inmersion-semestral', 'Inmersión semestral', 'inmersion', 'semestral', 6,  97200,  97,  'Seis meses de comunidad con 10% de descuento.', 3],
        ['inmersion-anual',     'Inmersión anual',     'inmersion', 'anual',     12, 172800, 173, 'Un año entero con 20% de descuento.', 4],
    ];
    foreach ($plans as [$slug, $name, $tier, $period, $months, $ars, $usd, $description, $sort]) {
        if (!Plan::findBy('slug', $slug)) {
            Plan::create(compact('slug', 'name', 'tier', 'period', 'months', 'description', 'sort') + ['price_ars' => $ars, 'price_usd' => $usd]);
        }
    }

    // Secciones de Contenidos para vos, con qué nivel las abre
    $both = ['orilla', 'inmersion'];
    $deep = ['inmersion'];
    $sections = [
        ['pispeando-arte', 'Pispeando Arte', 'crear', 'MIRAR PARA ESCRIBIR', 'eye', 0, $both,
            'Obras, artistas y rincones del arte para mirar con calma y dejar que despierten la escritura.'],
        ['tecniquitas', 'Tecniquitas', 'crear', 'HERRAMIENTAS', 'pen', 0, $both,
            'Pequeñas técnicas de escritura, una por vez, para practicar sin presión.'],
        ['runas-de-letras', 'Runas de Letras', 'crear', 'UNA CONSIGNA POR DÍA', 'sparkle', 0, $both,
            'Sacá una runa y dejate llevar por la consigna que trae. Una nueva cada día.'],
        ['materiales-para-leer', 'Materiales para leer', 'leer', 'LECTURAS', 'book', 0, $both,
            'Cuentos, poemas y fragmentos elegidos para leer despacio, con descarga en PDF.'],
        ['capituleando', 'Capituleando', 'leer', 'NOVELA POR ENTREGAS', 'layers', 0, $deep,
            'Una historia que se escribe capítulo a capítulo, en compañía.'],
        ['materiales-para-escuchar', 'Materiales para escuchar', 'leer', 'CHIRIMBITAS', 'headphones', 0, $deep,
            'Episodios breves para escuchar mientras caminás, cocinás o simplemente descansás.'],
        ['materiales-para-ver', 'Materiales para ver', 'leer', 'GRABACIONES', 'play', 0, $deep,
            'Las grabaciones de nuestros encuentros por Zoom y otros videos para ver cuando quieras.'],
        ['cafeteria-en-transito', 'Cafetería en tránsito', 'encontrarnos', 'ENCUENTROS EN VIVO', 'cup', 0, $deep,
            'Nuestra mesa compartida: encuentros en vivo para leernos y conversar.'],
        ['slow-mood', 'Slow Mood', 'encontrarnos', 'CARTAS SIN APURO', 'leaf', 1, $deep,
            'Cartas para bajar el ritmo y volver a lo simple.'],
        ['lugares', 'Lugares', 'encontrarnos', 'RELATOS DE VIAJE', 'pin', 1, $deep,
            'Rincones del mundo contados desde adentro, para viajar con las palabras.'],
        ['dejando-huellas', 'Dejando Huellas', 'encontrarnos', 'RELATOS DE LA COMUNIDAD', 'footprint', 1, $deep,
            'Historias de quienes forman parte de la comunidad y las huellas que van dejando.'],
    ];
    foreach ($sections as $sort => [$slug, $name, $group, $kicker, $icon, $animated, $tiers, $intro]) {
        if (Section::findBy('slug', $slug)) {
            continue;
        }
        $id = Section::create([
            'slug' => $slug, 'name' => $name, 'group_key' => $group, 'kicker' => $kicker,
            'icon' => $icon, 'animated_header' => $animated, 'intro' => $intro, 'sort' => $sort,
        ]);
        Section::setTiers($id, $tiers);
        seed_entries($id, $slug);
    }

    seed_runes();
    seed_books();
}

function seed_entries(int $sectionId, string $sectionSlug): void
{
    $body = "Este es un texto de ejemplo para ver cómo se lee una entrega completa. Sil va a reemplazarlo desde el panel de administración.\n\n"
        . "Escribir es, muchas veces, animarse a mirar dos veces lo mismo. La segunda mirada siempre trae algo que la primera dejó pasar: un color, una palabra, un silencio.\n\n"
        . "Te propongo que leas despacio, que subrayes lo que te resuene y que, si tenés ganas, escribas unas líneas al terminar.";

    $byKind = [
        'pispeando-arte'           => ['lectura',   ['Los azules de Hokusai', 'Una tarde con Frida', 'Lo que guardan las naturalezas muertas']],
        'tecniquitas'              => ['lectura',   ['Escribir con los cinco sentidos', 'El objeto que cuenta una historia', 'Diálogos que respiran']],
        'materiales-para-leer'     => ['lectura',   ['Cuentos de orilla', 'Poemas para la mañana', 'Fragmentos de mar']],
        'capituleando'             => ['lectura',   ['Capítulo 1 · La llegada', 'Capítulo 2 · El faro', 'Capítulo 3 · Marea baja']],
        'materiales-para-escuchar' => ['episodio',  ['Chirimbitas · Episodio 1', 'Chirimbitas · Episodio 2', 'Chirimbitas · Episodio 3']],
        'materiales-para-ver'      => ['grabacion', ['Encuentro de octubre', 'Encuentro de septiembre', 'Taller de cartas']],
        'cafeteria-en-transito'    => ['encuentro', ['Café de noviembre', 'Café de diciembre', 'Café de verano']],
        'slow-mood'                => ['carta',     ['Carta para un domingo lento', 'Carta sobre el silencio', 'Carta de las pequeñas cosas']],
        'lugares'                  => ['relato',    ['Mar del Plata en invierno', 'Un pueblo de la Patagonia', 'Las sierras al amanecer']],
        'dejando-huellas'          => ['relato',    ['El relato de Mainumbí', 'Volver a escribir a los sesenta', 'Cuadernos que viajan']],
        'runas-de-letras'          => ['lectura',   []],
    ];
    [$kind, $titles] = $byKind[$sectionSlug];

    foreach ($titles as $i => $title) {
        $published = date('Y-m-d H:i:s', strtotime("-{$i} weeks"));
        Database::insert('entries', [
            'section_id'   => $sectionId,
            'slug'         => slugify($title),
            'title'        => $title,
            'kind'         => $kind,
            'excerpt'      => 'Un adelanto breve de la entrega, para que sepas de qué se trata antes de abrirla.',
            'body'         => $body,
            'media_url'    => null,
            'event_at'     => $kind === 'encuentro' ? date('Y-m-d 19:00:00', strtotime('+' . (($i + 1) * 3) . ' weeks')) : null,
            'is_preview'   => $i === 0 ? 1 : 0,
            'is_published' => 1,
            'published_at' => $published,
            'created_at'   => $published,
            'updated_at'   => $published,
        ]);
    }
}

function seed_runes(): void
{
    if ((int) Database::value('SELECT COUNT(*) FROM runes') > 0) {
        return;
    }
    // Consignas provisorias: el banco definitivo lo escribe Sil.
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
    // Fichas provisorias: completar con los libros reales de Silvina.
    $books = [
        ['Título del primer libro', 'Novela', 2019],
        ['Título del segundo libro', 'Cuentos', 2021],
        ['Título del tercer libro', 'Cuaderno de escritura', 2024],
    ];
    foreach ($books as $i => [$title, $subtitle, $year]) {
        Database::insert('books', [
            'slug' => slugify($title), 'title' => $title, 'subtitle' => $subtitle, 'year' => $year,
            'publisher' => 'Editorial', 'pages' => 120 + $i * 40,
            'synopsis' => 'Sinopsis provisoria. Acá va la ficha del libro: de qué trata, para quién es y dónde conseguirlo.',
            'buy_url' => null, 'sort' => $i, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}

/** Miembros, membresías y pagos de prueba para ver el admin y el calendario con datos. */
function seed_demo(): void
{
    $people = [
        ['Lucía Fernández', 'inmersion-anual', -200, 'mercadopago'],
        ['Andrés Gómez', 'orilla-mensual', -25, 'mercadopago'],
        ['Valentina Ríos', 'inmersion-mensual', -10, 'paypal'],
        ['Sol Martínez', 'inmersion-semestral', -170, 'mercadopago'],
        ['Camila Sosa', 'orilla-mensual', -40, 'mercadopago'],
        ['Tomás Pereyra', 'inmersion-mensual', -3, 'mercadopago'],
        ['Ale Benítez', 'inmersion-anual', -350, 'paypal'],
        ['Inés Acosta', 'orilla-mensual', -18, 'paypal'],
    ];
    foreach ($people as [$name, $planSlug, $daysAgo, $gateway]) {
        $email = slugify($name) . '@ejemplo.com';
        if (User::findByEmail($email)) {
            continue;
        }
        $userId = User::create([
            'name' => $name, 'email' => $email, 'role' => 'member',
            'password_hash' => password_hash('miembro123', PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s', strtotime("{$daysAgo} days")),
        ]);
        $plan = Plan::findBy('slug', $planSlug);
        MembershipService::assignManual($userId, (int) $plan['id'], date('Y-m-d', strtotime("{$daysAgo} days")), true, $gateway, 'Dato de prueba');
    }
    echo "  Miembros de prueba: contraseña \"miembro123\"\n";
}
