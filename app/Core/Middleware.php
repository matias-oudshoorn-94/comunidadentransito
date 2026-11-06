<?php

declare(strict_types=1);

namespace App\Core;

/**
 * auth        · hay que haber ingresado al sitio
 * guest       · solo sin sesión del sitio (ingresar, registro)
 * admin       · hay que haber ingresado a la administración (/admin/ingresar)
 * admin_guest · solo sin sesión de administración
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
                if (!AdminAuth::check()) {
                    if ($request->wantsJson()) {
                        throw new HttpException(401);
                    }
                    if ($request->method === 'GET') {
                        Session::put('admin_intended', $request->path);
                    }
                    redirect('/admin/ingresar');
                }
                break;

            case 'admin_guest':
                if (AdminAuth::check()) {
                    redirect('/admin');
                }
                break;

            case 'guest':
                if (Auth::check()) {
                    redirect('/mi-comunidad');
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
