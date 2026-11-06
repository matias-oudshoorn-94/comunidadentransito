<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

/** Imágenes de la galería de una entrega */
final class EntryImage extends Model
{
    protected static string $table = 'entry_images';
    protected static bool $timestamps = false;

    public static function forEntry(int $entryId): array
    {
        return self::where('entry_id = ?', [$entryId], 'sort ASC, id ASC');
    }

    public static function add(int $entryId, string $path, ?string $caption = null): int
    {
        $sort = (int) Database::value('SELECT COALESCE(MAX(sort), 0) FROM entry_images WHERE entry_id = ?', [$entryId]) + 1;
        return self::create(['entry_id' => $entryId, 'path' => $path, 'caption' => $caption, 'sort' => $sort, 'created_at' => now()]);
    }
}
