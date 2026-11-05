<?php

declare(strict_types=1);

namespace App\Core;

use Throwable;

final class ErrorHandler
{
    public static function register(): void
    {
        error_reporting(E_ALL);
        ini_set('display_errors', config('app.debug') ? '1' : '0');

        set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
            if (!(error_reporting() & $severity)) {
                return false;
            }
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });

        set_exception_handler([self::class, 'handle']);
    }

    public static function handle(Throwable $e): void
    {
        $status = $e instanceof HttpException ? $e->status : 500;

        if ($status >= 500) {
            self::log($e);
        }
        if (PHP_SAPI === 'cli') {
            fwrite(STDERR, $e . PHP_EOL);
            exit(1);
        }

        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        http_response_code($status);

        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        if (str_contains($accept, 'application/json') || ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => $e instanceof HttpException ? $e->getMessage() : 'Error del servidor']);
            return;
        }

        if ($status >= 500 && config('app.debug')) {
            echo '<pre style="padding:2rem;color:#1E3D44;background:#FBF9F1;white-space:pre-wrap">'
                . e(get_class($e) . ': ' . $e->getMessage() . "\n\n" . $e->getTraceAsString()) . '</pre>';
            return;
        }

        $template = in_array($status, [403, 404, 419], true) ? "errors/{$status}" : 'errors/500';
        try {
            echo View::render($template, ['title' => 'Algo no salió bien', 'message' => $e->getMessage()], 'layouts/main');
        } catch (Throwable) {
            echo 'Algo no salió bien. Volvé a intentarlo en un momento.';
        }
    }

    public static function log(Throwable|string $entry): void
    {
        $line = '[' . date('Y-m-d H:i:s') . '] ' . ($entry instanceof Throwable ? (string) $entry : $entry) . PHP_EOL;
        @file_put_contents(BASE_PATH . '/storage/logs/app.log', $line, FILE_APPEND);
    }
}
