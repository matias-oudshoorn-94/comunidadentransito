<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

final class Subscriber extends Model
{
    protected static string $table = 'subscribers';

    public static function add(string $email, string $source): void
    {
        $email = strtolower(trim($email));
        if (!Database::value('SELECT 1 FROM subscribers WHERE email = ? AND source = ?', [$email, $source])) {
            self::create(['email' => $email, 'source' => $source]);
        }
    }
}
