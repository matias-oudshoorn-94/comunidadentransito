-- Comunidad en Tránsito · migración 2026-11-06 (2)
-- Galería de imágenes en las entregas.
--
-- phpMyAdmin → base u245081402_comunidad → pestaña SQL → pegar y Continuar.
-- Correla una sola vez (si la corrés de nuevo, la primera línea da "Duplicate column": no pasa nada).

ALTER TABLE entries ADD COLUMN gallery_title VARCHAR(160) NULL AFTER image_caption;

CREATE TABLE IF NOT EXISTS entry_images (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entry_id INT UNSIGNED NOT NULL,
    path VARCHAR(200) NOT NULL,
    caption VARCHAR(200) NULL,
    sort INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    INDEX idx_entry_images_entry (entry_id, sort),
    FOREIGN KEY (entry_id) REFERENCES entries(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
