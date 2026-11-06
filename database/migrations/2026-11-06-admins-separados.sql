-- Comunidad en Tránsito · migración 2026-11-06
-- Cuentas de administración separadas de las personas del sitio.
--
-- Para la base que ya está en Hostinger: phpMyAdmin → base u245081402_comunidad → pestaña SQL,
-- pegá todo esto y Continuar. Se puede correr una sola vez; no toca miembros, pagos ni contenidos.
-- Las cuentas que hoy tienen rol "admin" pasan a la tabla nueva con la misma contraseña.

CREATE TABLE IF NOT EXISTS admins (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    last_login_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO admins (name, email, password_hash, last_login_at, created_at, updated_at)
SELECT u.name, u.email, u.password_hash, u.last_login_at, u.created_at, NOW()
FROM users u
WHERE u.role = 'admin'
  AND NOT EXISTS (SELECT 1 FROM admins a WHERE a.email = u.email);

-- Ya no son personas del sitio (solo se borran si no tienen membresías ni pagos)
DELETE FROM users
WHERE role = 'admin'
  AND NOT EXISTS (SELECT 1 FROM memberships m WHERE m.user_id = users.id)
  AND NOT EXISTS (SELECT 1 FROM payments p WHERE p.user_id = users.id);

UPDATE users SET role = 'member' WHERE role = 'admin';
