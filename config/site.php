<?php

/*
 * Textos y fotos fijos del sitio. Las fotos van en public/assets/img/fotos/.
 * Si un archivo todavía no existe, se muestra una mancha de acuarela en su lugar.
 */
return [
    'tagline' => 'Un lugar para crear, leer y encontrarnos sin apuro.',

    'tiers' => [
        'orilla'    => ['name' => 'Orilla',    'blurb' => 'Para asomarte a la comunidad y empezar a escribir.'],
        'inmersion' => ['name' => 'Inmersión', 'blurb' => 'Todo lo que pasa en la comunidad, sin límites.'],
    ],

    'groups' => [
        'crear'        => 'Crear y escribir',
        'leer'         => 'Leer, ver y escuchar',
        'encontrarnos' => 'Encontrarnos',
    ],

    // Portada: 3 fotos de Sil con fundido suave
    'hero_photos' => [
        ['src' => 'fotos/sil-1.jpg', 'alt' => 'Silvina escribiendo junto a la ventana'],
        ['src' => 'fotos/sil-2.jpg', 'alt' => 'Silvina leyendo frente al mar'],
        ['src' => 'fotos/sil-3.jpg', 'alt' => 'Silvina en un encuentro de la comunidad'],
    ],

    // Tira de fotos de La Comunidad y Cafetería en tránsito
    'strip_photos' => [
        ['src' => 'fotos/comunidad-1.jpg', 'alt' => 'Encuentro de escritura'],
        ['src' => 'fotos/comunidad-2.jpg', 'alt' => 'Cuadernos y café'],
        ['src' => 'fotos/comunidad-3.jpg', 'alt' => 'Caminata por la costa'],
        ['src' => 'fotos/comunidad-4.jpg', 'alt' => 'Lectura compartida'],
        ['src' => 'fotos/comunidad-5.jpg', 'alt' => 'Taller de acuarela'],
        ['src' => 'fotos/comunidad-6.jpg', 'alt' => 'Mate y libros'],
    ],

    'faq' => [
        [
            'q' => '¿Cómo es la comunidad?',
            'a' => 'Es un espacio online para escribir, leer y compartir a tu ritmo. Cada semana hay contenidos nuevos y una vez por mes nos encontramos en vivo.',
        ],
        [
            'q' => '¿Qué diferencia hay entre Orilla e Inmersión?',
            'a' => 'Orilla te da acceso a las secciones para crear y escribir y a una parte de los materiales. Inmersión abre todo: grabaciones, encuentros, Slow Mood, Lugares y Dejando Huellas.',
        ],
        [
            'q' => '¿Cómo pago?',
            'a' => 'Podés pagar con Mercado Pago (en pesos, con renovación automática) o con PayPal (en dólares, desde cualquier país).',
        ],
        [
            'q' => '¿Puedo darme de baja cuando quiera?',
            'a' => 'Sí. Desde Mi cuenta cancelás la renovación con un clic y seguís teniendo acceso hasta el final del período que pagaste.',
        ],
        [
            'q' => '¿Necesito saber escribir?',
            'a' => 'Para nada. La comunidad es para cualquier persona con ganas de explorar la palabra, sin importar la experiencia.',
        ],
    ],

    'social' => [
        'instagram' => 'https://instagram.com/',
        'email'     => 'hola@comunidadentransito.com',
    ],
];
