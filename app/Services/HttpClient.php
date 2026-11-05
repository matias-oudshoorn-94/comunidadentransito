<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

/** Cliente HTTP mínimo con cURL para las APIs de pago. */
final class HttpClient
{
    /**
     * @return array{status:int, body:array, raw:string}
     */
    public static function request(string $method, string $url, array $headers = [], array|string|null $body = null): array
    {
        $ch = curl_init($url);
        $headerLines = [];
        foreach ($headers as $name => $value) {
            $headerLines[] = "{$name}: {$value}";
        }
        if (is_array($body)) {
            $body = json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $headerLines[] = 'Content-Type: application/json';
        }
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $headerLines,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);
        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }
        $raw = curl_exec($ch);
        if ($raw === false) {
            $error = curl_error($ch);
            throw new RuntimeException("No se pudo conectar con {$url}: {$error}");
        }
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $decoded = json_decode((string) $raw, true);

        return ['status' => $status, 'body' => is_array($decoded) ? $decoded : [], 'raw' => (string) $raw];
    }
}
