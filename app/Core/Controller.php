<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected string $layout = 'layouts/main';

    public function __construct(protected Request $request)
    {
    }

    protected function view(string $template, array $data = []): void
    {
        echo View::render($template, $data, $this->layout);
    }

    protected function redirect(string $to, ?string $toast = null): never
    {
        if ($toast !== null) {
            Session::flash('toast', $toast);
        }
        redirect($to);
    }

    protected function back(?string $toast = null): never
    {
        // Solo volvemos a páginas del propio sitio
        $referer = (string) $this->request->referer();
        $ownHost = explode(':', (string) ($_SERVER['HTTP_HOST'] ?? ''))[0];
        $path = '/';
        if ($referer !== '' && parse_url($referer, PHP_URL_HOST) === $ownHost) {
            $path = (parse_url($referer, PHP_URL_PATH) ?: '/');
            $query = parse_url($referer, PHP_URL_QUERY);
            $path .= $query ? '?' . $query : '';
        }
        $this->redirect($path, $toast);
    }

    protected function json(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /** Valida y, si falla, vuelve al formulario con los errores y lo escrito. */
    protected function validate(array $rules): array
    {
        $validator = Validator::make($this->request->all(), $rules);
        if ($validator->fails()) {
            if ($this->request->wantsJson()) {
                $this->json(['errors' => $validator->errors()], 422);
            }
            $old = $this->request->all();
            unset($old['password'], $old['password_confirmation'], $old['_token']);
            Session::flash('errors', $validator->errors());
            Session::flash('old', $old);
            $this->back();
        }
        return $validator->validated();
    }

    protected function abort(int $status): never
    {
        throw new HttpException($status);
    }
}
