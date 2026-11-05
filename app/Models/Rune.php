<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

final class Rune extends Model
{
    protected static string $table = 'runes';

    /** La runa que esta persona sacó hoy, si ya sacó. */
    public static function drawnToday(int $userId): ?array
    {
        return Database::one(
            'SELECT r.* FROM rune_draws d JOIN runes r ON r.id = d.rune_id WHERE d.user_id = ? AND d.drawn_on = ?',
            [$userId, date('Y-m-d')],
        );
    }

    /**
     * Saca una runa para hoy, evitando repetir las de los últimos días.
     * Si ya sacó (o hay dos pedidos al mismo tiempo), devuelve la misma.
     */
    public static function drawFor(int $userId): ?array
    {
        if ($rune = self::drawnToday($userId)) {
            return $rune;
        }
        $recent = Database::column('SELECT rune_id FROM rune_draws WHERE user_id = ? ORDER BY drawn_on DESC LIMIT 10', [$userId]);
        $pool = Database::column('SELECT id FROM runes WHERE is_active = 1');
        $fresh = array_values(array_diff($pool, $recent)) ?: $pool;
        if ($fresh === []) {
            return null;
        }
        $runeId = (int) $fresh[random_int(0, count($fresh) - 1)];
        try {
            Database::insert('rune_draws', ['user_id' => $userId, 'rune_id' => $runeId, 'drawn_on' => date('Y-m-d'), 'created_at' => now()]);
        } catch (\PDOException) {
            // Otro pedido ya guardó la runa de hoy
        }
        return self::drawnToday($userId);
    }
}
