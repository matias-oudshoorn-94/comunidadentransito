-- Esquema de Comunidad en Tránsito.
-- bin/migrate.php reemplaza {{PK}}, {{FK}}, {{TEXT}} y {{TABLE}} según el motor (SQLite o MySQL).

CREATE TABLE IF NOT EXISTS users (
    id {{PK}},
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'member',          -- siempre 'member' (la administración está en la tabla admins)
    phone VARCHAR(40) NULL,
    country VARCHAR(60) NULL,
    address VARCHAR(200) NULL,                            -- dirección para la carta Slow Mood
    city VARCHAR(120) NULL,
    postal_code VARCHAR(20) NULL,
    notes {{TEXT}} NULL,                                  -- notas internas del admin
    last_login_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
){{TABLE}};

-- Cuentas de administración: separadas de las personas del sitio, con su propio login en /admin/ingresar
CREATE TABLE IF NOT EXISTS admins (
    id {{PK}},
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    last_login_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
){{TABLE}};

-- Planes a la venta. tier = qué contenidos abre; months = duración del período.
CREATE TABLE IF NOT EXISTS plans (
    id {{PK}},
    slug VARCHAR(60) NOT NULL UNIQUE,
    name VARCHAR(120) NOT NULL,
    tier VARCHAR(20) NOT NULL,                            -- orilla | inmersion
    period VARCHAR(20) NOT NULL,                          -- mensual | semestral | anual
    months INTEGER NOT NULL,
    price_ars DECIMAL(12,2) NOT NULL DEFAULT 0,
    price_usd DECIMAL(12,2) NOT NULL DEFAULT 0,
    description {{TEXT}} NULL,
    is_active INTEGER NOT NULL DEFAULT 1,
    sort INTEGER NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
){{TABLE}};

CREATE TABLE IF NOT EXISTS memberships (
    id {{PK}},
    user_id {{FK}} NOT NULL,
    plan_id {{FK}} NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',        -- pending | active | expired | cancelled
    starts_at DATETIME NULL,
    ends_at DATETIME NULL,
    auto_renew INTEGER NOT NULL DEFAULT 0,
    gateway VARCHAR(20) NULL,                             -- mercadopago | paypal | manual
    gateway_subscription_id VARCHAR(120) NULL,            -- preapproval de Mercado Pago
    cancelled_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES plans(id)
){{TABLE}};
CREATE INDEX idx_memberships_ends ON memberships (ends_at);
CREATE INDEX idx_memberships_user ON memberships (user_id, status);

CREATE TABLE IF NOT EXISTS payments (
    id {{PK}},
    user_id {{FK}} NOT NULL,
    membership_id {{FK}} NULL,
    plan_id {{FK}} NULL,
    gateway VARCHAR(20) NOT NULL,                         -- mercadopago | paypal | manual | simulado
    gateway_reference VARCHAR(120) NULL,                  -- orden de PayPal / suscripción de MP
    gateway_payment_id VARCHAR(120) NULL UNIQUE,          -- id del cobro en la pasarela
    amount DECIMAL(12,2) NOT NULL,
    currency VARCHAR(3) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',        -- pending | approved | rejected | refunded
    paid_at DATETIME NULL,
    note VARCHAR(255) NULL,
    raw {{TEXT}} NULL,                                    -- última respuesta de la pasarela
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (membership_id) REFERENCES memberships(id) ON DELETE SET NULL,
    FOREIGN KEY (plan_id) REFERENCES plans(id)
){{TABLE}};
CREATE INDEX idx_payments_status ON payments (status, created_at);

-- Secciones de "Contenidos para vos"
CREATE TABLE IF NOT EXISTS sections (
    id {{PK}},
    slug VARCHAR(80) NOT NULL UNIQUE,
    name VARCHAR(120) NOT NULL,
    group_key VARCHAR(20) NOT NULL,                       -- crear | leer | encontrarnos
    kicker VARCHAR(80) NULL,
    intro {{TEXT}} NULL,
    icon VARCHAR(30) NULL,
    animated_header INTEGER NOT NULL DEFAULT 0,
    sort INTEGER NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
){{TABLE}};

-- Qué niveles de membresía abren cada sección (editable desde el admin)
CREATE TABLE IF NOT EXISTS section_tiers (
    section_id {{FK}} NOT NULL,
    tier VARCHAR(20) NOT NULL,
    PRIMARY KEY (section_id, tier),
    FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE CASCADE
){{TABLE}};

-- Entregas: la lectura, el episodio, la grabación, la carta o el relato
CREATE TABLE IF NOT EXISTS entries (
    id {{PK}},
    section_id {{FK}} NOT NULL,
    slug VARCHAR(160) NOT NULL,
    title VARCHAR(200) NOT NULL,
    kind VARCHAR(20) NOT NULL DEFAULT 'lectura',          -- lectura | episodio | grabacion | carta | relato | encuentro
    kicker VARCHAR(160) NULL,                             -- "PODCAST · CHIRIMBITAS · EPISODIO 1"
    meta VARCHAR(200) NULL,                               -- "Silvina Oudshoorn · duración a confirmar"
    hand_note VARCHAR(200) NULL,                          -- nota manuscrita bajo el título
    category VARCHAR(60) NULL,                            -- filtros y bloques dentro de una sección
    label VARCHAR(80) NULL,                               -- texto manuscrito de la tarjeta cuando no hay foto
    image VARCHAR(200) NULL,                              -- ruta dentro de public/assets/img
    image_caption VARCHAR(200) NULL,
    excerpt VARCHAR(400) NULL,
    body {{TEXT}} NULL,
    media_url VARCHAR(500) NULL,                          -- audio, video o PDF
    event_at DATETIME NULL,                               -- para encuentros ("Agendar")
    is_preview INTEGER NOT NULL DEFAULT 0,                -- "Así lo ve un miembro": visible para todo el mundo
    is_published INTEGER NOT NULL DEFAULT 1,
    published_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    UNIQUE (section_id, slug),
    FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE CASCADE
){{TABLE}};

-- Banco de consignas de Runas de Letras
CREATE TABLE IF NOT EXISTS runes (
    id {{PK}},
    symbol VARCHAR(8) NOT NULL,
    name VARCHAR(60) NOT NULL,
    meaning VARCHAR(120) NOT NULL,
    prompt {{TEXT}} NOT NULL,
    is_active INTEGER NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
){{TABLE}};

-- Una runa por día por persona
CREATE TABLE IF NOT EXISTS rune_draws (
    id {{PK}},
    user_id {{FK}} NOT NULL,
    rune_id {{FK}} NOT NULL,
    drawn_on DATE NOT NULL,
    created_at DATETIME NOT NULL,
    UNIQUE (user_id, drawn_on),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (rune_id) REFERENCES runes(id) ON DELETE CASCADE
){{TABLE}};

CREATE TABLE IF NOT EXISTS books (
    id {{PK}},
    slug VARCHAR(120) NOT NULL UNIQUE,
    title VARCHAR(200) NOT NULL,
    subtitle VARCHAR(200) NULL,
    year INTEGER NULL,
    publisher VARCHAR(120) NULL,
    pages INTEGER NULL,
    synopsis {{TEXT}} NULL,
    cover VARCHAR(200) NULL,
    price DECIMAL(12,2) NULL,
    is_new INTEGER NOT NULL DEFAULT 0,
    sold_out INTEGER NOT NULL DEFAULT 0,
    buy_url VARCHAR(500) NULL,
    sort INTEGER NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
){{TABLE}};

CREATE TABLE IF NOT EXISTS contact_messages (
    id {{PK}},
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL,
    subject VARCHAR(160) NULL,
    message {{TEXT}} NOT NULL,
    read_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
){{TABLE}};

-- Emails que pidieron el regalo de Tecniquitas
CREATE TABLE IF NOT EXISTS subscribers (
    id {{PK}},
    email VARCHAR(190) NOT NULL,
    source VARCHAR(60) NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    UNIQUE (email, source)
){{TABLE}};
