<?php

declare(strict_types=1);

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Session;
use App\Core\View;

function env(string $key, mixed $default = null): mixed
{
    $value = $_ENV[$key] ?? getenv($key);
    if ($value === false || $value === null) {
        return $default;
    }
    return match (strtolower((string) $value)) {
        'true'  => true,
        'false' => false,
        'null'  => null,
        default => $value,
    };
}

/** Lee config/{archivo}.php con notación de puntos: config('app.name'). */
function config(string $key, mixed $default = null): mixed
{
    static $cache = [];
    $parts = explode('.', $key);
    $file = array_shift($parts);
    if (!array_key_exists($file, $cache)) {
        $path = BASE_PATH . "/config/{$file}.php";
        $cache[$file] = is_file($path) ? require $path : [];
    }
    $value = $cache[$file];
    foreach ($parts as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return $default;
        }
        $value = $value[$part];
    }
    return $value;
}

function e(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Ruta interna del sitio. */
function url(string $path = ''): string
{
    return '/' . ltrim($path, '/');
}

/** URL absoluta, para emails y pasarelas de pago. */
function absolute_url(string $path = ''): string
{
    return config('app.url') . url($path);
}

function asset(string $path): string
{
    $file = BASE_PATH . '/public/assets/' . ltrim($path, '/');
    $version = is_file($file) ? (string) filemtime($file) : '1';
    return '/assets/' . ltrim($path, '/') . '?v=' . $version;
}

function asset_exists(string $path): bool
{
    return is_file(BASE_PATH . '/public/assets/' . ltrim($path, '/'));
}

function partial(string $template, array $data = []): string
{
    return View::capture($template, $data);
}

function redirect(string $to): never
{
    header('Location: ' . $to, true, 302);
    exit;
}

function csrf_token(): string
{
    return Csrf::token();
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(Csrf::token()) . '">';
}

function old(string $key, mixed $default = ''): string
{
    $old = Session::getFlash('old', []);
    return (string) ($old[$key] ?? $default);
}

/** Primer error de validación de un campo. */
function error(string $key): ?string
{
    $errors = Session::getFlash('errors', []);
    return $errors[$key][0] ?? null;
}

function flash(string $key, mixed $default = null): mixed
{
    return Session::getFlash($key, $default);
}

function auth(): ?array
{
    return Auth::user();
}

function is_admin(): bool
{
    return Auth::isAdmin();
}

function current_path(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    return $path === '/' ? '/' : rtrim($path, '/');
}

/** Para marcar el link activo del menú. */
function is_path(string $prefix): bool
{
    $path = current_path();
    return $prefix === '/' ? $path === '/' : ($path === $prefix || str_starts_with($path, $prefix . '/'));
}

function now(): string
{
    return date('Y-m-d H:i:s');
}

function money(float|int|string|null $amount, string $currency = 'ARS'): string
{
    $amount = (float) $amount;
    $decimals = fmod($amount, 1.0) === 0.0 ? 0 : 2;
    $formatted = number_format($amount, $decimals, ',', '.');
    return $currency === 'USD' ? 'US$ ' . $formatted : '$ ' . $formatted;
}

const MESES = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
const DIAS = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];

/** "5 de noviembre de 2026" (sin depender de ext-intl). */
function fecha(?string $date, bool $withTime = false): string
{
    if (!$date) {
        return '—';
    }
    $ts = strtotime($date);
    if ($ts === false) {
        return '—';
    }
    $text = date('j', $ts) . ' de ' . MESES[(int) date('n', $ts) - 1] . ' de ' . date('Y', $ts);
    return $withTime ? $text . ', ' . date('H:i', $ts) . ' h' : $text;
}

function fecha_corta(?string $date): string
{
    return $date ? date('d/m/Y', strtotime($date)) : '—';
}

function mes_nombre(int $month): string
{
    return MESES[$month - 1];
}

/** Recorta texto UTF-8 sin cortar palabras. */
function excerpt(?string $text, int $length = 140): string
{
    $text = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $text)) ?? '');
    $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    if (count($chars) <= $length) {
        return $text;
    }
    $cut = implode('', array_slice($chars, 0, $length));
    $cut = preg_replace('/\s+\S*$/u', '', $cut) ?? $cut;
    return rtrim($cut, ' ,.;:') . '…';
}

function slugify(string $text): string
{
    $text = strtr($text, [
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
        'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u', 'Ü' => 'u', 'Ñ' => 'n',
    ]);
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
    return trim($text, '-');
}

/** Párrafos simples a partir de texto plano (el contenido lo escribe Sil desde el admin). */
function paragraphs(?string $text): string
{
    $blocks = preg_split('/\R{2,}/u', trim((string) $text)) ?: [];
    $html = '';
    foreach ($blocks as $block) {
        if ($block !== '') {
            $html .= '<p>' . nl2br(e($block)) . '</p>';
        }
    }
    return $html;
}

/**
 * Íconos de línea fina (1,4 px), todos en un viewBox de 24.
 */
function icon(string $name, string $class = 'icon'): string
{
    $paths = [
        'lock'     => '<rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/>',
        'unlock'   => '<rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 7.5-2"/>',
        'arrow'    => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'back'     => '<path d="M19 12H5M11 6l-6 6 6 6"/>',
        'menu'     => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'close'    => '<path d="M6 6l12 12M18 6L6 18"/>',
        'plus'     => '<path d="M12 5v14M5 12h14"/>',
        'minus'    => '<path d="M5 12h14"/>',
        'chevron'  => '<path d="M6 9l6 6 6-6"/>',
        'user'     => '<circle cx="12" cy="8" r="4"/><path d="M4 20c1.5-4 4.5-6 8-6s6.5 2 8 6"/>',
        'calendar' => '<rect x="4" y="5" width="16" height="15" rx="2"/><path d="M4 10h16M9 3v4M15 3v4"/>',
        'play'     => '<circle cx="12" cy="12" r="9"/><path d="M10 8.5l5 3.5-5 3.5z"/>',
        'download' => '<path d="M12 4v11M7 10l5 5 5-5M5 20h14"/>',
        'mail'     => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/>',
        'heart'    => '<path d="M12 20s-7-4.4-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 10c0 5.6-7 10-7 10z"/>',
        'pen'      => '<path d="M4 20l4-1 11-11-3-3L5 16z"/><path d="M14 6l3 3"/>',
        'book'     => '<path d="M4 5a2 2 0 0 1 2-2h13v16H6a2 2 0 0 0-2 2z"/><path d="M4 19V5"/>',
        'eye'      => '<path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
        'headphones' => '<path d="M4 15v-3a8 8 0 0 1 16 0v3"/><rect x="3" y="14" width="4" height="6" rx="1.5"/><rect x="17" y="14" width="4" height="6" rx="1.5"/>',
        'cup'      => '<path d="M5 9h11v5a5 5 0 0 1-5 5h-1a5 5 0 0 1-5-5z"/><path d="M16 10h1.5a2.5 2.5 0 0 1 0 5H16M8 3v3M11 3v3"/>',
        'wave'     => '<path d="M2 14c2.5 0 2.5-3 5-3s2.5 3 5 3 2.5-3 5-3 2.5 3 5 3"/><path d="M2 19c2.5 0 2.5-3 5-3s2.5 3 5 3 2.5-3 5-3 2.5 3 5 3" opacity=".6"/>',
        'leaf'     => '<path d="M5 19c0-8 5-14 15-14 0 10-6 15-14 15"/><path d="M5 19l8-8"/>',
        'pin'      => '<path d="M12 21s-6-5.6-6-11a6 6 0 0 1 12 0c0 5.4-6 11-6 11z"/><circle cx="12" cy="10" r="2.2"/>',
        'footprint'=> '<path d="M8 3c2 0 3 2 3 5s-1 5-3 5-3-2-3-5 1-5 3-5zM16 11c2 0 3 2 3 5s-1 5-3 5-3-2-3-5 1-5 3-5z"/>',
        'sparkle'  => '<path d="M12 3v4M12 17v4M3 12h4M17 12h4M6 6l2.5 2.5M15.5 15.5L18 18M6 18l2.5-2.5M15.5 8.5L18 6"/>',
        'check'    => '<path d="M5 12.5l4.5 4.5L19 7"/>',
        'logout'   => '<path d="M15 4h3a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-3M10 16l-4-4 4-4M6 12h10"/>',
        'home'     => '<path d="M4 11l8-7 8 7v9H4z"/><path d="M10 20v-5h4v5"/>',
        'users'    => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 19c1-3.5 3.5-5 6.5-5s5.5 1.5 6.5 5"/><circle cx="17" cy="9" r="2.5"/><path d="M17 14c2.5 0 4 1.5 4.5 4"/>',
        'card'     => '<rect x="3" y="6" width="18" height="12" rx="2"/><path d="M3 10h18M7 15h3"/>',
        'layers'   => '<path d="M12 4l9 5-9 5-9-5z"/><path d="M3 14l9 5 9-5"/>',
        'folder'   => '<path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>',
        'tag'      => '<path d="M3 12V4h8l10 10-8 8z"/><circle cx="7.5" cy="8.5" r="1.3"/>',
        'external' => '<path d="M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/>',
        'search'   => '<circle cx="11" cy="11" r="6"/><path d="M20 20l-4.5-4.5"/>',
    ];
    $body = $paths[$name] ?? $paths['sparkle'];
    return '<svg class="' . e($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $body . '</svg>';
}

/**
 * Foto del sitio. Si el archivo todavía no está subido, muestra un marco de acuarela
 * con el texto alternativo, así el diseño no se rompe.
 */
function photo(string $src, string $alt, string $class = ''): string
{
    if (asset_exists('img/' . $src)) {
        return '<img class="' . e($class) . '" src="' . e(asset('img/' . $src)) . '" alt="' . e($alt) . '" loading="lazy">';
    }
    $hue = abs(crc32($src)) % 3;
    return '<div class="photo-ph photo-ph--' . $hue . ' ' . e($class) . '" role="img" aria-label="' . e($alt) . '"><span>' . e($alt) . '</span></div>';
}

/** Texto en castellano para los estados de membresías y pagos. */
function estado(string $status): string
{
    return [
        'active'    => 'Activa',
        'pending'   => 'Pendiente',
        'expired'   => 'Vencida',
        'cancelled' => 'Cancelada',
        'approved'  => 'Aprobado',
        'rejected'  => 'Rechazado',
        'refunded'  => 'Reintegrado',
    ][$status] ?? $status;
}

function badge(string $status): string
{
    return '<span class="badge badge--' . e($status) . '">' . e(estado($status)) . '</span>';
}

function medio(?string $gateway): string
{
    return \App\Services\Payments\GatewayFactory::LABELS[$gateway ?? ''] ?? (string) $gateway;
}
