<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

final class Payment extends Model
{
    protected static string $table = 'payments';

    private const SELECT = 'SELECT pay.*, u.name AS user_name, u.email AS user_email, p.name AS plan_name
        FROM payments pay JOIN users u ON u.id = pay.user_id LEFT JOIN plans p ON p.id = pay.plan_id';

    public static function list(string $status = '', string $gateway = '', string $from = '', string $to = ''): array
    {
        $where = ['1 = 1'];
        $params = [];
        if ($status !== '') {
            $where[] = 'pay.status = ?';
            $params[] = $status;
        }
        if ($gateway !== '') {
            $where[] = 'pay.gateway = ?';
            $params[] = $gateway;
        }
        if ($from !== '') {
            $where[] = 'pay.created_at >= ?';
            $params[] = $from . ' 00:00:00';
        }
        if ($to !== '') {
            $where[] = 'pay.created_at <= ?';
            $params[] = $to . ' 23:59:59';
        }
        return Database::all(self::SELECT . ' WHERE ' . implode(' AND ', $where) . ' ORDER BY pay.created_at DESC LIMIT 500', $params);
    }

    public static function forUser(int $userId): array
    {
        return Database::all(self::SELECT . ' WHERE pay.user_id = ? ORDER BY pay.created_at DESC', [$userId]);
    }

    public static function recent(int $limit = 8): array
    {
        return Database::all(self::SELECT . " ORDER BY pay.created_at DESC LIMIT {$limit}");
    }

    public static function approvedBetween(string $from, string $to): array
    {
        return Database::all(self::SELECT . " WHERE pay.status = 'approved' AND pay.paid_at BETWEEN ? AND ? ORDER BY pay.paid_at", [$from, $to]);
    }

    /** Total cobrado por moneda: ['ARS' => 123, 'USD' => 45] */
    public static function totals(string $from, string $to): array
    {
        $rows = Database::all(
            "SELECT currency, SUM(amount) AS total FROM payments WHERE status = 'approved' AND paid_at BETWEEN ? AND ? GROUP BY currency",
            [$from, $to],
        );
        return array_column($rows, 'total', 'currency');
    }

    public static function findByGatewayPaymentId(string $id): ?array
    {
        return self::findBy('gateway_payment_id', $id);
    }
}
