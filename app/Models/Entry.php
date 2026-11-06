<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

final class Entry extends Model
{
    protected static string $table = 'entries';

    public const KINDS = [
        'lectura'   => 'Lectura',
        'episodio'  => 'Episodio',
        'grabacion' => 'Grabación',
        'carta'     => 'Carta',
        'relato'    => 'Relato',
        'encuentro' => 'Encuentro',
    ];

    /** Entregas publicadas de una sección: primero la de muestra, después las más nuevas. */
    public static function forSection(int $sectionId): array
    {
        return self::withImages(self::where(
            'section_id = ? AND is_published = 1 AND published_at <= ?',
            [$sectionId, now()],
            'is_preview DESC, published_at DESC',
        ));
    }

    public static function bySlug(int $sectionId, string $slug): ?array
    {
        $row = Database::one(
            'SELECT * FROM entries WHERE section_id = ? AND slug = ? AND is_published = 1 AND published_at <= ?',
            [$sectionId, $slug, now()],
        );
        return $row ? self::withImages([$row])[0] : null;
    }

    /** Últimas entregas de las secciones indicadas (para Mi Comunidad). */
    public static function latestIn(array $sectionIds, int $limit = 6): array
    {
        if ($sectionIds === []) {
            return [];
        }
        $in = implode(',', array_fill(0, count($sectionIds), '?'));
        return self::withImages(Database::all(
            "SELECT e.*, s.slug AS section_slug, s.name AS section_name FROM entries e JOIN sections s ON s.id = e.section_id
             WHERE e.section_id IN ({$in}) AND e.is_published = 1 AND e.published_at <= ?
             ORDER BY e.published_at DESC LIMIT {$limit}",
            [...array_map('intval', $sectionIds), now()],
        ));
    }

    public static function upcomingEvents(array $sectionIds, int $limit = 3): array
    {
        if ($sectionIds === []) {
            return [];
        }
        $in = implode(',', array_fill(0, count($sectionIds), '?'));
        return self::withImages(Database::all(
            "SELECT e.*, s.slug AS section_slug, s.name AS section_name FROM entries e JOIN sections s ON s.id = e.section_id
             WHERE e.section_id IN ({$in}) AND e.is_published = 1 AND e.event_at >= ?
             ORDER BY e.event_at ASC LIMIT {$limit}",
            [...array_map('intval', $sectionIds), now()],
        ));
    }

    /** Sin imagen subida y con video de YouTube: usa la miniatura del video. */
    private static function withImages(array $rows): array
    {
        foreach ($rows as &$row) {
            $row['image'] = entry_image($row);
        }
        return $rows;
    }

    public static function adminList(int $sectionId = 0): array
    {
        $sql = 'SELECT e.*, s.name AS section_name, s.slug AS section_slug FROM entries e JOIN sections s ON s.id = e.section_id';
        $params = [];
        if ($sectionId > 0) {
            $sql .= ' WHERE e.section_id = ?';
            $params[] = $sectionId;
        }
        return Database::all($sql . ' ORDER BY s.sort ASC, e.published_at DESC', $params);
    }

    public static function uniqueSlug(int $sectionId, string $title, int $ignoreId = 0): string
    {
        $base = slugify($title) ?: 'entrega';
        $slug = $base;
        $n = 2;
        while (Database::value('SELECT 1 FROM entries WHERE section_id = ? AND slug = ? AND id <> ?', [$sectionId, $slug, $ignoreId])) {
            $slug = $base . '-' . $n++;
        }
        return $slug;
    }
}
