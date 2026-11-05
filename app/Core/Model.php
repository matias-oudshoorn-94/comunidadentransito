<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Modelo base: las filas se devuelven como arrays asociativos.
 * Los nombres de columna/orden vienen siempre del código, nunca del usuario.
 */
abstract class Model
{
    protected static string $table;
    protected static bool $timestamps = true;

    public static function find(int|string|null $id): ?array
    {
        if ($id === null || $id === '') {
            return null;
        }
        return Database::one('SELECT * FROM `' . static::$table . '` WHERE id = ?', [(int) $id]);
    }

    public static function findOrFail(int|string|null $id): array
    {
        return static::find($id) ?? throw new HttpException(404);
    }

    public static function findBy(string $column, mixed $value): ?array
    {
        return Database::one('SELECT * FROM `' . static::$table . "` WHERE `{$column}` = ? LIMIT 1", [$value]);
    }

    public static function all(string $orderBy = 'id DESC'): array
    {
        return Database::all('SELECT * FROM `' . static::$table . "` ORDER BY {$orderBy}");
    }

    public static function where(string $where, array $params = [], string $orderBy = 'id DESC', ?int $limit = null): array
    {
        $sql = 'SELECT * FROM `' . static::$table . "` WHERE {$where} ORDER BY {$orderBy}";
        if ($limit !== null) {
            $sql .= ' LIMIT ' . $limit;
        }
        return Database::all($sql, $params);
    }

    public static function count(string $where = '1 = 1', array $params = []): int
    {
        return (int) Database::value('SELECT COUNT(*) FROM `' . static::$table . "` WHERE {$where}", $params);
    }

    public static function create(array $data): int
    {
        if (static::$timestamps) {
            $data['created_at'] ??= now();
            $data['updated_at'] ??= now();
        }
        return Database::insert(static::$table, $data);
    }

    public static function update(int $id, array $data): void
    {
        if (static::$timestamps) {
            $data['updated_at'] = now();
        }
        Database::update(static::$table, $data, 'id = ?', [$id]);
    }

    public static function delete(int $id): void
    {
        Database::run('DELETE FROM `' . static::$table . '` WHERE id = ?', [$id]);
    }
}
