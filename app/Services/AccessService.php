<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

/**
 * Decide qué puede ver cada persona.
 * Cada sección tiene una lista de niveles (orilla, inmersion) que la abren;
 * una persona accede si tiene una membresía activa de alguno de esos niveles.
 */
final class AccessService
{
    private static array $tierCache = [];

    /** Niveles vigentes de la persona. Administración ve todo. */
    public static function tiersFor(?array $user): array
    {
        if ($user === null) {
            return [];
        }
        if ($user['role'] === 'admin') {
            return array_keys(config('site.tiers'));
        }
        $id = (int) $user['id'];
        return self::$tierCache[$id] ??= Database::column(
            "SELECT DISTINCT p.tier FROM memberships m JOIN plans p ON p.id = m.plan_id
             WHERE m.user_id = ? AND m.status = 'active' AND m.starts_at <= ? AND m.ends_at >= ?",
            [$id, now(), now()],
        );
    }

    public static function isMember(?array $user): bool
    {
        return self::tiersFor($user) !== [];
    }

    public static function canAccessSection(?array $user, array $section): bool
    {
        return array_intersect(self::tiersFor($user), $section['tiers'] ?? []) !== [];
    }

    /** Las entregas marcadas como muestra ("Así lo ve un miembro") son públicas. */
    public static function canViewEntry(?array $user, array $section, array $entry): bool
    {
        return (int) $entry['is_preview'] === 1 || self::canAccessSection($user, $section);
    }

    public static function forget(int $userId): void
    {
        unset(self::$tierCache[$userId]);
    }
}
