<?php

/*
 * Presentación de cada sección de "Contenidos para vos", tal como está en Figma.
 * Lo que cambia seguido (entregas, quién accede) se maneja desde el admin; esto es el armado de la página.
 *
 * hand      · frase manuscrita del encabezado        image   · foto del encabezado (dentro de assets/img)
 * card      · texto corto de la tarjeta (Contenidos) chip    · etiqueta de la tarjeta
 * chips     · datos del encabezado [ícono, título, detalle]
 * block     · bloque de entregas [kicker, título, texto]
 * action    · botón de la tarjeta abierta            second  · link secundario de la tarjeta abierta
 * more      · link al final de una entrega           cta     · franja final [manuscrita, título, botón, fondo]
 */
return [
    'pispeando-arte' => [
        'hand' => 'un recorrido como de museo', 'image' => 'fotos/mesa-acuarelas.jpg', 'frame' => 'polaroid',
        'image_caption' => 'Obra destacada · Silvina Oudshoorn',
        'card' => 'Obras y una artista invitada cada mes.', 'chip' => 'Crear', 'card_image' => 'fotos/magnolia.jpg',
        'chips' => [[null, '1 entrega', 'por mes'], [null, 'Para miembros', 'Inmersión']],
        'block' => ['ENTREGAS', 'Todas las entregas', null],
        'layout' => 'featured', 'action' => 'Leer la entrega', 'more' => 'Ver todas las entregas',
        'cta' => ['una muestra nueva cada mes', 'Sumate para ver todas las entregas', 'Ver membresías', 'sky'],
    ],
    'tecniquitas' => [
        'hand' => 'para experimentar en tus tiempos', 'image' => 'fotos/mesa-acuarelas.jpg',
        'card' => 'Técnicas para experimentar en casa.', 'chip' => 'Crear', 'card_image' => 'fotos/mesa-acuarelas.jpg',
        'chips' => [['doc', 'Láminas', 'para imprimir'], ['palette', 'Se renueva', 'cada mes']],
        'block' => ['PARA MIEMBROS', 'Una Tecniquita nueva cada mes', 'Cada mes se renueva con una propuesta nueva y láminas sobre la técnica.'],
        'layout' => 'tecniquitas', 'columns' => 2, 'action' => 'Ver la Tecniquita', 'more' => 'Ver todas las Tecniquitas',
        'cta' => ['técnicas que, con el tiempo, nos liberan', 'Sumate para recibir una Tecniquita cada mes', 'Quiero sumarme', 'sand'],
    ],
    'runas-de-letras' => [
        'hand' => 'runa significa “susurrar”', 'card' => 'Un mazo para inspirar tu escritura.', 'chip' => 'Crear',
        'card_image' => 'fotos/runa-dibujo.jpg',
    ],
    'materiales-para-leer' => [
        'hand' => 'para ir armando tu biblioteca', 'image' => 'fotos/papeles-pasto.jpg',
        'card' => 'Lecturas y capítulos de sus libros.', 'chip' => 'Leer', 'card_image' => 'fotos/papeles-pasto.jpg',
        'block' => ['PARA MIEMBROS', 'La biblioteca', 'Lecturas elegidas por Silvina, para leer a tu ritmo.'],
        'filters' => [
            'autocuidados' => 'Autocuidados', 'espiritualidad' => 'Espiritualidad', 'cuidados-paliativos' => 'Cuidados paliativos',
            'teorias-y-evidencia' => 'Teorías y evidencia', 'articulos-interesantes' => 'Artículos interesantes',
        ],
        'promo' => 'capituleando', 'action' => 'Leer', 'second' => 'Descargar PDF →', 'more' => 'Ver más lecturas',
        'cta' => ['para leer sin apuro', 'Sumate para acceder a la biblioteca', 'Quiero sumarme', 'sand'],
    ],
    'capituleando' => [
        'hand' => 'un capítulo de regalo por mes', 'image' => 'fotos/libros-playa.jpg',
        'card' => 'Un capítulo de sus libros, cada mes.', 'chip' => 'Leer', 'card_image' => 'fotos/libros-playa.jpg',
        'chips' => [['doc', 'PDF', 'para leer o imprimir'], ['palette', 'Se renueva', 'cada mes']],
        'block' => ['PARA MIEMBROS', 'Un capítulo nuevo cada mes', 'Cada mes, Silvina elige un capítulo de uno de sus libros.'],
        'cover' => 'book', 'action' => 'Leer el capítulo', 'second' => 'Descargar PDF →', 'more' => 'Ver todos los capítulos',
        'cta' => ['para leer sin apuro', 'Sumate para recibir un capítulo cada mes', 'Quiero sumarme', 'sand'],
    ],
    'materiales-para-escuchar' => [
        'hand' => 'pequeñas historias para el oído', 'image' => 'fotos/sil-cafe-flores.jpg',
        'card' => 'Chirimbitas, el podcast de Silvina.', 'chip' => 'Escuchar', 'card_image' => 'fotos/sil-cafe-flores.jpg',
        'block' => ['PARA MIEMBROS', 'Chirimbitas', 'La palabra original es chiribita: una chispa que brilla y enciende la vida, y también una pequeña margarita silvestre. En la Comunidad tenés el podcast entero, el backstage y los bloopers.'],
        'play' => true, 'action' => 'Escuchar', 'second' => 'Ver el backstage →', 'more' => 'Más episodios',
        'detail_button' => 'Ver el backstage',
        'cta' => ['para escuchar con calma', 'Sumate para escuchar Chirimbitas completo', 'Quiero sumarme', 'sand'],
    ],
    'materiales-para-ver' => [
        'hand' => 'para ver con calma', 'image' => 'fotos/cafe-taza.jpg',
        'card' => 'Encuentros por Zoom y videos.', 'chip' => 'Ver', 'card_image' => 'fotos/cafe-taza.jpg',
        'blocks' => [
            ['encuentro', 'PARA MIEMBROS', 'Encuentros por Zoom', 'Te invito a participar de un encuentro por Zoom mensual, sincrónico (o queda grabado), en el que iremos abordando diferentes temáticas motivadoras.', 3],
            ['video', 'SELECCIÓN DE SILVINA', 'Videos que Silvina recomienda', 'Una selección de videos de otros autores que me resultaron interesantes. Cada mes sumo uno más.', 2],
        ],
        'play' => true, 'action' => 'Ver la grabación', 'more' => 'Más encuentros',
        'cta' => ['para ver con calma', 'Sumate para ver los encuentros', 'Quiero sumarme', 'sand'],
    ],
    'cafeteria-en-transito' => [
        'hand' => '¿querés un cafecito?', 'image' => 'fotos/sil-cafe-flores.jpg',
        'card' => 'Charlas en el grupo de WhatsApp.', 'chip' => 'Encuentros', 'card_image' => 'fotos/collage-cafes.jpg',
        'strip' => ['fotos/cafe-taza.jpg', 'fotos/sil-cafe-flores.jpg', 'fotos/collage-cafes.jpg', 'fotos/sil-sonrisa.jpg', 'fotos/sil-retrato.jpg', 'fotos/sil-rosa.jpg', 'fotos/comedor.jpg'],
        'block' => ['PARA MIEMBROS', 'Un lugarcito para encontrarnos', 'Ni bien ingreses a la Comunidad en Tránsito, te sumamos al grupo de WhatsApp para favorecer el contacto e intercambio cercano y permanente.'],
        'action' => 'Ir al grupo', 'more' => 'Volver a la Cafetería',
        'cta' => ['con un cafecito de por medio', 'Sumate y te esperamos en la Cafetería', 'Quiero sumarme', 'sand'],
    ],
    'slow-mood' => [
        'hand' => 'una carta para leer sin apuro', 'image' => 'fotos/magnolia-cuadrada.jpg',
        'card' => 'Una carta escrita por Silvina.', 'chip' => 'Encuentros', 'card_image' => 'fotos/comedor.jpg',
        'block' => ['PARA MIEMBROS', 'Las cartas', 'Cada mes, una carta nueva de Silvina.'],
        'action' => 'Leer la carta', 'more' => 'Ver todas las cartas',
        'cta' => ['para leer sin apuro', 'Sumate para recibir la carta de cada mes', 'Quiero sumarme', 'sand'],
    ],
    'lugares' => [
        'hand' => 'relatos de viajes desde la emoción', 'image' => 'fotos/flores-lilas.jpg',
        'card' => 'Un lugar por mes, contado desde la emoción.', 'chip' => 'Encuentros', 'card_image' => 'fotos/mainumbi-1.jpg',
        'block' => ['PARA MIEMBROS', 'Entrega del mes', 'Cada mes, un lugar contado por Silvina, con sus fotos.'],
        'action' => 'Leer el relato', 'second' => 'Ver las fotos →', 'more' => 'Ver todos los lugares',
        'cta' => ['para viajar sin moverte', 'Sumate para recibir un lugar cada mes', 'Quiero sumarme', 'sand'],
    ],
    'dejando-huellas' => [
        'hand' => 'relatos que dejan huella', 'image' => 'fotos/jardin-sendero.jpg',
        'card' => 'Relatos de personas que dejaron huella.', 'chip' => 'Encuentros', 'card_image' => 'fotos/jardin-sendero.jpg',
        'block' => ['PARA MIEMBROS', 'Los relatos', 'Historias que remiten a experiencias que pueden ser replicadas y sentidas.'],
        'action' => 'Leer el relato', 'more' => 'Ver todos los relatos',
        'cta' => ['historias que inspiran', 'Sumate para leer un relato cada mes', 'Quiero sumarme', 'sand'],
    ],
];
