<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Envío de emails simple. En desarrollo (MAIL_DRIVER=log) los guarda en storage/logs/mail.log.
 * Para producción conviene cambiar a un proveedor SMTP/API (Brevo, Mailgun, etc.).
 */
final class Mailer
{
    public static function send(string $to, string $subject, string $body): bool
    {
        if (config('app.mail_driver') === 'log') {
            $entry = sprintf("[%s] Para: %s\nAsunto: %s\n\n%s\n%s\n", now(), $to, $subject, $body, str_repeat('─', 60));
            return file_put_contents(BASE_PATH . '/storage/logs/mail.log', $entry, FILE_APPEND) !== false;
        }

        $from = (string) config('app.mail_from');
        $headers = [
            'From: ' . config('app.name') . ' <' . $from . '>',
            'Reply-To: ' . $from,
            'Content-Type: text/plain; charset=UTF-8',
        ];
        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        return mail($to, $encodedSubject, $body, implode("\r\n", $headers));
    }
}
