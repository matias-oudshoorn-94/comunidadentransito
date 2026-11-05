<?php

declare(strict_types=1);

/*
 * Exporta la base actual (normalmente la SQLite de desarrollo) como un .sql para MySQL/MariaDB,
 * listo para importar desde phpMyAdmin.
 *
 *   php bin/export-mysql.php database/export/comunidad.sql
 *
 * Para generar una base limpia de producción (sin miembros de prueba):
 *   DB_SQLITE_PATH=/tmp/limpia.sqlite ADMIN_PASSWORD='clave-segura' php bin/migrate.php --fresh --seed
 *   DB_SQLITE_PATH=/tmp/limpia.sqlite php bin/export-mysql.php database/export/comunidad.sql
 */

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Database;

$output = $argv[1] ?? null;
if (!$output) {
    fwrite(STDERR, "Uso: php bin/export-mysql.php ruta/salida.sql\n");
    exit(1);
}

// Orden en que se crean (las tablas con claves foráneas van después de las que referencian)
$tables = ['users', 'plans', 'memberships', 'payments', 'sections', 'section_tiers', 'entries', 'runes', 'rune_draws', 'books', 'contact_messages', 'subscribers'];

$schema = strtr((string) file_get_contents(BASE_PATH . '/database/schema.sql'), [
    '{{PK}}'    => 'INT UNSIGNED AUTO_INCREMENT PRIMARY KEY',
    '{{FK}}'    => 'INT UNSIGNED',
    '{{TEXT}}'  => 'TEXT',
    '{{TABLE}}' => ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
]);
$schema = preg_replace('/--[^\n]*/', '', $schema) ?? $schema;
$schema = str_replace('CREATE TABLE IF NOT EXISTS', 'CREATE TABLE', $schema);
$statements = array_filter(array_map('trim', explode(';', $schema)));

$quote = static function (mixed $value): string {
    if ($value === null) {
        return 'NULL';
    }
    if (is_int($value) || is_float($value)) {
        return (string) $value;
    }
    return "'" . str_replace(['\\', "'", "\0", "\n", "\r", "\x1a"], ['\\\\', "\\'", '\\0', '\\n', '\\r', '\\Z'], (string) $value) . "'";
};

$sql = [];
$sql[] = '-- Comunidad en Tránsito · base inicial para MySQL / MariaDB';
$sql[] = '-- Generado el ' . date('Y-m-d H:i') . '. Importar en una base vacía desde phpMyAdmin.';
$sql[] = '-- ATENCIÓN: borra y vuelve a crear las tablas. No lo importes sobre una base con datos reales.';
$sql[] = '';
$sql[] = 'SET NAMES utf8mb4;';
$sql[] = 'SET FOREIGN_KEY_CHECKS = 0;';
$sql[] = "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';";
$sql[] = '';
foreach (array_reverse($tables) as $table) {
    $sql[] = "DROP TABLE IF EXISTS `{$table}`;";
}
$sql[] = '';
foreach ($statements as $statement) {
    $sql[] = $statement . ';';
    $sql[] = '';
}

foreach ($tables as $table) {
    $rows = Database::all("SELECT * FROM `{$table}`");
    if ($rows === []) {
        continue;
    }
    $columns = '`' . implode('`, `', array_keys($rows[0])) . '`';
    $sql[] = "-- {$table}";
    foreach (array_chunk($rows, 50) as $chunk) {
        $values = array_map(fn (array $row) => '(' . implode(', ', array_map($quote, $row)) . ')', $chunk);
        $sql[] = "INSERT INTO `{$table}` ({$columns}) VALUES\n" . implode(",\n", $values) . ';';
    }
    $sql[] = '';
}

$sql[] = 'SET FOREIGN_KEY_CHECKS = 1;';
$sql[] = '';

if (!is_dir(dirname($output))) {
    mkdir(dirname($output), 0775, true);
}
file_put_contents($output, implode("\n", $sql));
echo "Exportado: {$output}\n";
