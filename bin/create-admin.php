<?php

declare(strict_types=1);

/*
 * Crea una cuenta de administración (o le cambia la contraseña si el email ya existe).
 *
 *   php bin/create-admin.php
 *
 * Pide nombre, email y contraseña. La contraseña no se muestra mientras se escribe.
 */

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Models\Admin;

if (PHP_SAPI !== 'cli') {
    exit("Este comando se usa desde la consola.\n");
}

$ask = static function (string $label, bool $secret = false): string {
    echo $label . ': ';
    if ($secret && DIRECTORY_SEPARATOR === '/') {
        system('stty -echo');
        $value = trim((string) fgets(STDIN));
        system('stty echo');
        echo "\n";
        return $value;
    }
    return trim((string) fgets(STDIN));
};

$email = strtolower($ask('Email'));
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exit("Ese email no es válido.\n");
}
$existing = Admin::findByEmail($email);
$name = $existing['name'] ?? $ask('Nombre');
if ($name === '') {
    exit("El nombre es obligatorio.\n");
}
$password = $ask('Contraseña (al menos 10 caracteres)', true);
if (strlen($password) < 10) {
    exit("La contraseña tiene que tener al menos 10 caracteres.\n");
}
if ($ask('Repetila', true) !== $password) {
    exit("Las contraseñas no coinciden.\n");
}

if ($existing) {
    Admin::update((int) $existing['id'], ['password_hash' => password_hash($password, PASSWORD_DEFAULT)]);
    echo "Contraseña actualizada para {$email}.\n";
} else {
    Admin::register($name, $email, $password);
    echo "Listo: {$email} ya puede ingresar en " . absolute_url('/admin/ingresar') . "\n";
}
