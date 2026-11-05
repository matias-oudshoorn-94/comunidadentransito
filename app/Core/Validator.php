<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Reglas: required, email, min:n, max:n, numeric, date, in:a,b,c, same:campo, unique:tabla,columna[,idIgnorado]
 */
final class Validator
{
    private const MESSAGES = [
        'required' => 'Este campo es obligatorio.',
        'email'    => 'Revisá que el email esté bien escrito.',
        'min'      => 'Tiene que tener al menos :n caracteres.',
        'max'      => 'Puede tener hasta :n caracteres.',
        'numeric'  => 'Tiene que ser un número.',
        'date'     => 'Tiene que ser una fecha válida.',
        'in'       => 'Elegí una opción válida.',
        'same'     => 'Los valores no coinciden.',
        'unique'   => 'Ya existe una cuenta con este dato.',
    ];

    /** @var array<string, list<string>> */
    private array $errors = [];
    private array $validated = [];

    private function __construct(private readonly array $data)
    {
    }

    public static function make(array $data, array $rules): self
    {
        $validator = new self($data);
        foreach ($rules as $field => $fieldRules) {
            $validator->check($field, is_array($fieldRules) ? $fieldRules : explode('|', $fieldRules));
        }
        return $validator;
    }

    private function check(string $field, array $rules): void
    {
        $raw = $this->data[$field] ?? null;
        $value = is_string($raw) ? trim($raw) : $raw;
        $empty = $value === null || $value === '' || $value === [];

        foreach ($rules as $rule) {
            [$name, $arg] = array_pad(explode(':', $rule, 2), 2, null);

            if ($name === 'required') {
                if ($empty) {
                    $this->fail($field, 'required');
                    return;
                }
                continue;
            }
            if ($name === 'nullable' || $empty) {
                continue;
            }

            $ok = match ($name) {
                'email'   => filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
                'min'     => strlen((string) $value) >= (int) $arg,
                'max'     => strlen((string) $value) <= (int) $arg,
                'numeric' => is_numeric($value),
                'date'    => strtotime((string) $value) !== false,
                'in'      => in_array((string) $value, explode(',', (string) $arg), true),
                'same'    => $value === trim((string) ($this->data[$arg] ?? '')),
                'unique'  => $this->isUnique((string) $arg, $value),
                default   => throw new \LogicException("Regla desconocida: {$name}"),
            };
            if (!$ok) {
                $this->fail($field, $name, $arg);
                return;
            }
        }
        $this->validated[$field] = $value;
    }

    private function isUnique(string $arg, mixed $value): bool
    {
        $parts = explode(',', $arg);
        [$table, $column] = $parts;
        $ignoreId = isset($parts[2]) ? (int) $parts[2] : 0;
        return !Database::value(
            "SELECT 1 FROM `{$table}` WHERE `{$column}` = ? AND id <> ? LIMIT 1",
            [is_string($value) ? strtolower($value) : $value, $ignoreId],
        );
    }

    private function fail(string $field, string $rule, ?string $arg = null): void
    {
        $this->errors[$field][] = str_replace(':n', (string) $arg, self::MESSAGES[$rule]);
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function validated(): array
    {
        return $this->validated;
    }
}
