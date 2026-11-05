<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;
use Throwable;

/** Plantillas PHP en app/Views. El layout recibe el HTML de la vista en $content. */
final class View
{
    public static function render(string $template, array $data = [], ?string $layout = null): string
    {
        $content = self::capture($template, $data);
        if ($layout === null) {
            return $content;
        }
        return self::capture($layout, [...$data, 'content' => $content]);
    }

    public static function capture(string $template, array $data = []): string
    {
        $__file = BASE_PATH . '/app/Views/' . $template . '.php';
        if (!is_file($__file)) {
            throw new RuntimeException("No existe la vista: {$template}");
        }
        extract($data, EXTR_SKIP);
        ob_start();
        try {
            include $__file;
        } catch (Throwable $e) {
            ob_end_clean();
            throw $e;
        }
        return (string) ob_get_clean();
    }
}
