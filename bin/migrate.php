<?php

declare(strict_types=1);

/*
 * Crea las tablas y, opcionalmente, carga datos iniciales.
 *
 *   php bin/migrate.php            crea las tablas si no existen
 *   php bin/migrate.php --seed     además carga planes, secciones, runas y una cuenta admin
 *   php bin/migrate.php --demo     además carga miembros y pagos de prueba (solo para desarrollo)
 *   php bin/migrate.php --fresh    borra todo y vuelve a crear (pide confirmación en producción)
 */

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Database;

$args = array_slice($argv, 1);
$fresh = in_array('--fresh', $args, true);
$demo = in_array('--demo', $args, true);
$seed = $demo || in_array('--seed', $args, true);

$driver = Database::driver();

if ($driver === 'sqlite') {
    $path = (string) config('database.sqlite_path');
    $path = str_starts_with($path, '/') ? $path : BASE_PATH . '/' . $path;
    if (!is_file($path)) {
        touch($path);
    }
}

$pdo = Database::pdo();
$tables = ['subscribers', 'contact_messages', 'books', 'rune_draws', 'runes', 'entries', 'section_tiers', 'sections', 'payments', 'memberships', 'plans', 'users'];

if ($fresh) {
    if (config('app.env') === 'production') {
        echo "Estás en producción. Escribí 'borrar todo' para continuar: ";
        if (trim((string) fgets(STDIN)) !== 'borrar todo') {
            exit("Cancelado.\n");
        }
    }
    if ($driver === 'mysql') {
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
    }
    foreach ($tables as $table) {
        $pdo->exec("DROP TABLE IF EXISTS `{$table}`");
    }
    if ($driver === 'mysql') {
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    }
    echo "Tablas borradas.\n";
}

$exists = $driver === 'sqlite'
    ? Database::value("SELECT name FROM sqlite_master WHERE type = 'table' AND name = 'users'")
    : Database::value("SHOW TABLES LIKE 'users'");

if (!$exists) {
    $replacements = $driver === 'sqlite'
        ? ['{{PK}}' => 'INTEGER PRIMARY KEY AUTOINCREMENT', '{{FK}}' => 'INTEGER', '{{TEXT}}' => 'TEXT', '{{TABLE}}' => '']
        : ['{{PK}}' => 'INT UNSIGNED AUTO_INCREMENT PRIMARY KEY', '{{FK}}' => 'INT UNSIGNED', '{{TEXT}}' => 'TEXT', '{{TABLE}}' => ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'];

    $sql = strtr((string) file_get_contents(BASE_PATH . '/database/schema.sql'), $replacements);
    $sql = preg_replace('/--[^\n]*/', '', $sql) ?? $sql;
    foreach (array_filter(array_map('trim', explode(';', $sql))) as $statement) {
        $pdo->exec($statement);
    }
    echo "Tablas creadas ({$driver}).\n";
} else {
    echo "Las tablas ya existen. Usá --fresh para recrearlas.\n";
}

if ($seed) {
    require BASE_PATH . '/database/seed.php';
    seed_base();
    echo "Datos iniciales cargados.\n";
}
if ($demo) {
    if (config('app.env') === 'production') {
        exit("No se cargan datos de prueba en producción.\n");
    }
    seed_demo();
    echo "Datos de prueba cargados.\n";
}
