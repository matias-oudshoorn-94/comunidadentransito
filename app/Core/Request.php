<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    private ?array $jsonBody = null;

    public function __construct(
        public readonly string $method,
        public readonly string $path,
        private readonly array $query,
        private readonly array $body,
        private readonly array $server,
        private readonly string $rawBody,
    ) {
    }

    public static function capture(): self
    {
        $path = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
        $path = $path === '/' ? '/' : rtrim($path, '/');

        return new self(
            strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET'),
            $path,
            $_GET,
            $_POST,
            $_SERVER,
            (string) file_get_contents('php://input'),
        );
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $this->json()[$key] ?? $this->query[$key] ?? $default;
    }

    public function string(string $key, string $default = ''): string
    {
        $value = $this->input($key, $default);
        return is_scalar($value) ? trim((string) $value) : $default;
    }

    public function int(string $key, int $default = 0): int
    {
        $value = $this->input($key);
        return is_numeric($value) ? (int) $value : $default;
    }

    public function bool(string $key): bool
    {
        return in_array($this->input($key), ['1', 1, true, 'on', 'true'], true);
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->query, $this->json(), $this->body);
    }

    public function only(string ...$keys): array
    {
        $all = $this->all();
        return array_intersect_key($all, array_flip($keys));
    }

    public function header(string $name): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return $this->server[$key] ?? ($name === 'Content-Type' ? ($this->server['CONTENT_TYPE'] ?? null) : null);
    }

    public function raw(): string
    {
        return $this->rawBody;
    }

    public function json(): array
    {
        if ($this->jsonBody === null) {
            $isJson = str_contains((string) $this->header('Content-Type'), 'application/json');
            $decoded = $isJson ? json_decode($this->rawBody, true) : null;
            $this->jsonBody = is_array($decoded) ? $decoded : [];
        }
        return $this->jsonBody;
    }

    public function wantsJson(): bool
    {
        return str_contains((string) $this->header('Accept'), 'application/json')
            || $this->header('X-Requested-With') === 'fetch';
    }

    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    public function referer(): ?string
    {
        return $this->server['HTTP_REFERER'] ?? null;
    }
}
