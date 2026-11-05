<?php

declare(strict_types=1);

namespace App\Core;

/**
 * auth   · hay que haber ingresado
 * admin  · solo administración
 * guest  · solo sin sesión (ingresar, registro)
 * csrf   · se aplica solo a todo POST salvo rutas con "nocsrf" (webhooks)
 */
final class Middleware
{
    public static function handle(string $name, Request $request): void
    {
        switch ($name) {
            case 'auth':
                if (!Auth::check()) {
                    self::toLogin($request);
                }
                break;

            case 'admin':
                if (!Auth::check()) {
                    self::toLogin($request);
                }
                if (!Auth::isAdmin()) {
                    throw new HttpException(403);
                }
                break;

            case 'guest':
                if (Auth::check()) {
                    redirect(Auth::isAdmin() ? '/admin' : '/mi-comunidad');
                }
                break;

            case 'csrf':
                if ($_POST === [] && $_FILES === [] && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0 && !$request->json()) {
                    throw new HttpException(413, 'El archivo es más grande de lo que permite el servidor (' . \App\Services\Uploader::serverLimit() . ').');
                }
                $token = $request->input('_token') ?? $request->header('X-CSRF-Token');
                if (!Csrf::verify(is_string($token) ? $token : null)) {
                    throw new HttpException(419, 'La sesión expiró. Volvé a intentarlo.');
                }
                break;

            case 'nocsrf':
                break;

            default:
                throw new \LogicException("Middleware desconocido: {$name}");
        }
    }

    private static function toLogin(Request $request): never
    {
        if ($request->wantsJson()) {
            throw new HttpException(401);
        }
        if ($request->method === 'GET') {
            Session::put('intended', $request->path);
        }
        Session::flash('toast', 'Ingresá para continuar.');
        redirect('/ingresar');
    }
}
