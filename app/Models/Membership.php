<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

final class Membership extends Model
{
    protected static string $table = 'memberships';

    private const SELECT = 'SELECT m.*, p.name AS plan_name, p.tier, p.period, p.months, u.name AS user_name, u.email AS user_email
        FROM memberships m JOIN plans p ON p.id = m.plan_id JOIN users u ON u.id = m.user_id';

    /** Membresía vigente hoy (si tiene varias, la que vence más tarde). */
    public static function currentFor(int $userId): ?array
    {
        return Database::one(
            self::SELECT . " WHERE m.user_id = ? AND m.status = 'active' AND m.starts_at <= ? AND m.ends_at >= ? ORDER BY m.ends_at DESC LIMIT 1",
            [$userId, now(), now()],
        );
    }

    public static function forUser(int $userId): array
    {
        return Database::all(self::SELECT . " WHERE m.user_id = ? AND m.status <> 'pending' ORDER BY m.ends_at DESC", [$userId]);
    }

    public static function withDetails(int $id): ?array
    {
        return Database::one(self::SELECT . ' WHERE m.id = ?', [$id]);
    }

    public static function list(string $status = '', string $tier = ''): array
    {
        $where = ["m.status <> 'pending'"];
        $params = [];
        if ($status === 'por-vencer') {
            $where[] = "m.status = 'active' AND m.ends_at BETWEEN ? AND ?";
            $params[] = now();
            $params[] = date('Y-m-d 23:59:59', strtotime('+30 days'));
        } elseif ($status !== '') {
            $where[] = 'm.status = ?';
            $params[] = $status;
        }
        if ($tier !== '') {
            $where[] = 'p.tier = ?';
            $params[] = $tier;
        }
        return Database::all(self::SELECT . ' WHERE ' . implode(' AND ', $where) . ' ORDER BY m.ends_at ASC', $params);
    }

    /** Vencimientos y altas entre dos fechas, para el calendario. */
    public static function endingBetween(string $from, string $to): array
    {
        return Database::all(self::SELECT . " WHERE m.status IN ('active', 'expired', 'cancelled') AND m.ends_at BETWEEN ? AND ? ORDER BY m.ends_at", [$from, $to]);
    }

    public static function startingBetween(string $from, string $to): array
    {
        return Database::all(self::SELECT . " WHERE m.status <> 'pending' AND m.starts_at BETWEEN ? AND ? ORDER BY m.starts_at", [$from, $to]);
    }

    public static function expiringSoon(int $days = 30, int $limit = 8): array
    {
        return Database::all(
            self::SELECT . " WHERE m.status = 'active' AND m.ends_at BETWEEN ? AND ? ORDER BY m.ends_at ASC LIMIT {$limit}",
            [now(), date('Y-m-d 23:59:59', strtotime("+{$days} days"))],
        );
    }

    public static function activeCount(?string $tier = null): int
    {
        $sql = "SELECT COUNT(DISTINCT m.user_id) FROM memberships m JOIN plans p ON p.id = m.plan_id WHERE m.status = 'active' AND m.starts_at <= ? AND m.ends_at >= ?";
        $params = [now(), now()];
        if ($tier !== null) {
            $sql .= ' AND p.tier = ?';
            $params[] = $tier;
        }
        return (int) Database::value($sql, $params);
    }
}
