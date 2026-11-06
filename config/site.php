<?php

/*
 * Textos fijos del sitio (de Figma). Las fotos están en public/assets/img/.
 */
return [
    'tagline' => 'Hacer cosas que te hacen bien.',

    'tiers' => [
        'orilla'    => ['name' => 'Orilla',    'blurb' => 'Para asomarte y probar.',
                        'features' => ['Carta Slow Mood', 'Webinars', 'Cafetería en tránsito']],
        'inmersion' => ['name' => 'Inmersión', 'blurb' => 'Toda la Comunidad, sin límites.',
                        'features' => ['Todo lo de Orilla', 'Todos los contenidos', 'Capítulo de regalo', '25% en libros', 'Asesoramiento (a definir)']],
    ],

    'groups' => [
        'crear'        => 'Crear y escribir',
        'leer'         => 'Leer, ver y escuchar',
        'encontrarnos' => 'Encontrarnos',
    ],

    'social' => [
        'whatsapp'        => '5492235936781',
        'whatsapp_label'  => '223 593-6781',
        'instagram'       => 'https://instagram.com/silvinaoudshoorn',
        'instagram_label' => '@silvinaoudshoorn',
        'facebook'        => 'https://facebook.com/silvinaouds',
        'facebook_label'  => 'silvinaouds',
        'email'           => 'hola@comunidadentransito.com',
        // Link de invitación al grupo de WhatsApp de la Cafetería (solo se muestra a miembros)
        'whatsapp_group'  => '',
    ],

    // Video "¿Qué es la terapia ocupacional?" (YouTube o Vimeo). Vacío: se muestra la foto.
    'about_video' => 'https://www.youtube.com/watch?v=qpajIQQo-x0',

    'testimonials' => [
        ['Fuiste inspiración única y en el momento justo.', 'Agustina · terapista ocupacional'],
        ['Tiene un apellido difícil, sonrisa fácil y da unos abrazos que abrazan.', 'Vivi · terapista ocupacional'],
        ['Maravillosos libros llenos de amor, sabiduría y estrategias.', 'Lectora de sus libros'],
    ],

    'faq' => [
        ['q' => '¿Necesito saber de arte?', 'a' => 'Para nada. Las propuestas están pensadas para cualquier persona con ganas de crear, escribir o leer, sin experiencia previa.'],
        ['q' => '¿Cómo pago?', 'a' => 'Con Mercado Pago, en pesos y con renovación automática. Si estás fuera de Argentina, podés pagar con PayPal.'],
        ['q' => '¿Puedo cancelar cuando quiera?', 'a' => 'Sí. Desde Mi cuenta cancelás con un clic y seguís teniendo acceso hasta el final del período pagado.'],
        ['q' => '¿A dónde llega la carta?', 'a' => 'La carta Slow Mood llega por correo a la dirección que cargás al sumarte. También queda guardada en la Comunidad.'],
        ['q' => '¿Los encuentros quedan grabados?', 'a' => 'Sí. Cada encuentro por Zoom queda grabado en Materiales para ver, para que lo mires cuando quieras.'],
    ],

    'faq_payments' => [
        ['q' => '¿Puedo cambiar de plan?', 'a' => 'Sí, cuando quieras desde Mi cuenta. El cambio se aplica en el próximo cobro.'],
        ['q' => '¿Cómo cancelo?', 'a' => 'Desde Mi cuenta, con un clic. Seguís teniendo acceso hasta el final del período pagado.'],
        ['q' => '¿Cuándo se cobra?', 'a' => 'El primer cobro es el día que te sumás; después se renueva automáticamente cada mes, semestre o año según el plan.'],
        ['q' => '¿A dónde llega la carta?', 'a' => 'A la dirección que cargás al sumarte. La podés cambiar cuando quieras desde Mi cuenta.'],
    ],
];
