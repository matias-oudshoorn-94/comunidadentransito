<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

final class Section extends Model
{
    protected static string $table = 'sections';

    private static ?array $cache = null;

    /** Todas las secciones con sus niveles, ordenadas. Se cachea por pedido (el menú las usa en cada página). */
    public static function allWithTiers(): array
    {
        if (self::$cache === null) {
            $tiers = [];
            foreach (Database::all('SELECT section_id, tier FROM section_tiers') as $row) {
                $tiers[$row['section_id']][] = $row['tier'];
            }
            self::$cache = array_map(
                fn (array $s) => $s + ['tiers' => $tiers[$s['id']] ?? []],
                self::all('sort ASC'),
            );
        }
        return self::$cache;
    }

    /** [group_key => [secciones]] en el orden del menú */
    public static function grouped(): array
    {
        $groups = array_fill_keys(array_keys(config('site.groups')), []);
        foreach (self::allWithTiers() as $section) {
            $groups[$section['group_key']][] = $section;
        }
        return $groups;
    }

    public static function bySlug(string $slug): ?array
    {
        foreach (self::allWithTiers() as $section) {
            if ($section['slug'] === $slug) {
                return $section;
            }
        }
        return null;
    }

    public static function setTiers(int $sectionId, array $tiers): void
    {
        Database::run('DELETE FROM section_tiers WHERE section_id = ?', [$sectionId]);
        foreach (array_unique($tiers) as $tier) {
            Database::insert('section_tiers', ['section_id' => $sectionId, 'tier' => $tier]);
        }
        self::$cache = null;
    }
}
