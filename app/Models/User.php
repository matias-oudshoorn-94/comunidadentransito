<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

final class User extends Model
{
    protected static string $table = 'users';

    public static function findByEmail(string $email): ?array
    {
        return self::findBy('email', strtolower(trim($email)));
    }

    public static function register(string $name, string $email, string $password): int
    {
        return self::create([
            'name'          => trim($name),
            'email'         => strtolower(trim($email)),
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role'          => 'member',
        ]);
    }

    /**
     * Listado del admin con la membresía vigente (o la última) de cada persona.
     * $status: '' | activos | vencidos | sin-membresia | admin
     */
    public static function search(string $q = '', string $status = '', int $page = 1, int $perPage = 25): array
    {
        $where = ['1 = 1'];
        $params = [];
        if ($q !== '') {
            $where[] = '(u.name LIKE ? OR u.email LIKE ?)';
            $params[] = "%{$q}%";
            $params[] = "%{$q}%";
        }
        $current = "(SELECT m.id FROM memberships m WHERE m.user_id = u.id AND m.status <> 'pending' ORDER BY m.ends_at DESC LIMIT 1)";
        $where[] = match ($status) {
            'activos'       => "EXISTS (SELECT 1 FROM memberships m WHERE m.user_id = u.id AND m.status = 'active' AND m.ends_at >= ?)",
            'vencidos'      => "NOT EXISTS (SELECT 1 FROM memberships m WHERE m.user_id = u.id AND m.status = 'active' AND m.ends_at >= ?) AND EXISTS (SELECT 1 FROM memberships m WHERE m.user_id = u.id AND m.status <> 'pending')",
            'sin-membresia' => "NOT EXISTS (SELECT 1 FROM memberships m WHERE m.user_id = u.id AND m.status <> 'pending')",
            default         => '1 = 1',
        };
        if (in_array($status, ['activos', 'vencidos'], true)) {
            $params[] = now();
        }
        $whereSql = implode(' AND ', $where);

        $total = (int) Database::value("SELECT COUNT(*) FROM users u WHERE {$whereSql}", $params);
        $offset = max(0, ($page - 1) * $perPage);
        $rows = Database::all(
            "SELECT u.*, m.status AS membership_status, m.ends_at, m.auto_renew, p.name AS plan_name, p.tier
             FROM users u
             LEFT JOIN memberships m ON m.id = {$current}
             LEFT JOIN plans p ON p.id = m.plan_id
             WHERE {$whereSql}
             ORDER BY u.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params,
        );

        return ['rows' => $rows, 'total' => $total, 'page' => $page, 'pages' => max(1, (int) ceil($total / $perPage))];
    }
}
