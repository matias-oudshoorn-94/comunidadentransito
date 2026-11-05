<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

/**
 * Archivos que sube el admin (imágenes, audios, videos y PDF).
 * Se guardan en public/uploads/AAAA/MM/ con nombre aleatorio. El tipo se verifica
 * por el contenido real del archivo, no solo por la extensión.
 */
final class Uploader
{
    /** tipo => [extensiones permitidas por MIME, tamaño máximo en MB] */
    private const TYPES = [
        'image' => [[
            'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif',
        ], 15],
        'media' => [[
            'audio/mpeg' => 'mp3', 'audio/mp4' => 'm4a', 'audio/x-m4a' => 'm4a', 'audio/aac' => 'aac',
            'audio/ogg' => 'ogg', 'audio/wav' => 'wav', 'audio/x-wav' => 'wav',
            'video/mp4' => 'mp4', 'video/webm' => 'webm', 'video/quicktime' => 'mov',
            'application/pdf' => 'pdf',
        ], 1024],
    ];

    public const BASE = 'uploads';

    /** ¿Vino un archivo en este campo? */
    public static function has(?array $file): bool
    {
        return is_array($file) && ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
    }

    /**
     * Guarda el archivo y devuelve su ruta pública relativa ("uploads/2026/11/abc123.jpg").
     * @throws RuntimeException con un mensaje para mostrar
     */
    public static function store(array $file, string $type): string
    {
        [$allowed, $maxMb] = self::TYPES[$type] ?? throw new \LogicException("Tipo desconocido: {$type}");

        $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;
        if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
            throw new RuntimeException('El archivo es más grande de lo que permite el servidor (' . self::serverLimit() . ').');
        }
        if ($error !== UPLOAD_ERR_OK || !is_uploaded_file((string) $file['tmp_name'])) {
            throw new RuntimeException('No se pudo subir el archivo. Probá de nuevo.');
        }
        if ($file['size'] > $maxMb * 1024 * 1024) {
            throw new RuntimeException("El archivo pesa más de {$maxMb} MB.");
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']) ?: '';
        if (!isset($allowed[$mime])) {
            $what = $type === 'image' ? 'una imagen JPG, PNG o WebP' : 'un audio (MP3, M4A), un video (MP4) o un PDF';
            throw new RuntimeException("Ese archivo no es {$what}.");
        }

        $folder = self::BASE . '/' . date('Y/m');
        $dir = BASE_PATH . '/public/' . $folder;
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new RuntimeException('No se pudo crear la carpeta de archivos en el servidor.');
        }
        $original = pathinfo((string) $file['name'], PATHINFO_FILENAME);
        $name = substr(slugify($original) ?: 'archivo', 0, 40) . '-' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
        if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
            throw new RuntimeException('No se pudo guardar el archivo en el servidor.');
        }
        @chmod($dir . '/' . $name, 0644);

        return $folder . '/' . $name;
    }

    /** Borra un archivo subido (solo dentro de public/uploads). */
    public static function delete(?string $path): void
    {
        $path = ltrim((string) $path, '/');
        if (!str_starts_with($path, self::BASE . '/') || str_contains($path, '..')) {
            return;
        }
        $file = BASE_PATH . '/public/' . $path;
        if (is_file($file)) {
            @unlink($file);
        }
    }

    /** Tamaño máximo que acepta PHP, para mostrar en el admin. */
    public static function serverLimit(): string
    {
        $toBytes = static function (string $v): int {
            $n = (int) $v;
            return match (strtoupper(substr(trim($v), -1))) { 'G' => $n << 30, 'M' => $n << 20, 'K' => $n << 10, default => $n };
        };
        $bytes = min($toBytes((string) ini_get('upload_max_filesize')), $toBytes((string) ini_get('post_max_size')));
        return $bytes >= 1 << 30 ? round($bytes / (1 << 30), 1) . ' GB' : round($bytes / (1 << 20)) . ' MB';
    }
}
