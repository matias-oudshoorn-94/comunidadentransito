<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\Admin;

/**
 * Sesión de administración. Usa su propia clave de sesión (admin_id), independiente
 * de la de las personas del sitio (user_id): entrar o salir de una no afecta a la otra.
 */
final class AdminAuth
{
    private static ?array $admin = null;
    private static bool $loaded = false;

    public static function user(): ?array
    {
        if (!self::$loaded) {
            self::$loaded = true;
            $id = Session::get('admin_id');
            self::$admin = $id ? Admin::find((int) $id) : null;
        }
        return self::$admin;
    }

    public static function id(): ?int
    {
        return isset(self::user()['id']) ? (int) self::user()['id'] : null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function attempt(string $email, string $password): bool
    {
        $admin = Admin::findByEmail($email);
        // Se verifica igual con un hash falso para no revelar si el email existe por el tiempo de respuesta
        $hash = $admin['password_hash'] ?? '$2y$12$abcdefghijklmnopqrstuuVJdXk8JmQ3mCQdQ6Ue1J9Gk1yWx2e6u';
        if (!password_verify($password, $hash) || !$admin) {
            return false;
        }
        if (password_needs_rehash($admin['password_hash'], PASSWORD_DEFAULT)) {
            Admin::update((int) $admin['id'], ['password_hash' => password_hash($password, PASSWORD_DEFAULT)]);
        }
        self::login($admin);
        return true;
    }

    public static function login(array $admin): void
    {
        Session::regenerate();
        Session::put('admin_id', (int) $admin['id']);
        Admin::update((int) $admin['id'], ['last_login_at' => now()]);
        self::$admin = Admin::find((int) $admin['id']);
        self::$loaded = true;
    }

    public static function logout(): void
    {
        Session::forget('admin_id');
        Session::regenerate();
        self::$admin = null;
    }

    public static function refresh(): void
    {
        self::$loaded = false;
    }
}
