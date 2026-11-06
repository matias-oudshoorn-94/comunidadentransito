<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Cuentas de administración (tabla admins, separada de users). */
final class Admin extends Model
{
    protected static string $table = 'admins';

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
        ]);
    }
}
