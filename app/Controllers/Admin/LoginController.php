<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminAuth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\Admin;

/**
 * Ingreso a la administración (/admin/ingresar), separado del ingreso del sitio.
 * Si todavía no hay ninguna cuenta de administración, la misma pantalla permite crear la primera.
 */
final class LoginController extends Controller
{
    protected string $layout = 'layouts/admin-auth';

    public function form(): void
    {
        $this->view('admin/login', [
            'title' => 'Ingresar a la administración',
            'setup' => Admin::count() === 0,
        ]);
    }

    public function login(): void
    {
        if (Admin::count() === 0) {
            $this->setup();
        }

        $data = $this->validate(['email' => 'required|email', 'password' => 'required']);

        // Después de 5 intentos fallidos, una pausa de 5 minutos
        $lockedUntil = (int) Session::get('admin_login_locked_until', 0);
        if ($lockedUntil > time()) {
            Session::flash('old', ['email' => $data['email']]);
            $this->back('Demasiados intentos. Esperá unos minutos y volvé a probar.');
        }
        if (!AdminAuth::attempt($data['email'], (string) $this->request->input('password'))) {
            $attempts = (int) Session::get('admin_login_attempts', 0) + 1;
            Session::put('admin_login_attempts', $attempts);
            if ($attempts >= 5) {
                Session::put('admin_login_locked_until', time() + 300);
                Session::put('admin_login_attempts', 0);
            }
            Session::flash('errors', ['email' => ['El email o la contraseña no coinciden.']]);
            Session::flash('old', ['email' => $data['email']]);
            $this->back();
        }
        Session::forget('admin_login_attempts');

        $intended = Session::get('admin_intended');
        Session::forget('admin_intended');
        $this->redirect(is_string($intended) && str_starts_with($intended, '/admin') ? $intended : '/admin');
    }

    /** Primera cuenta de administración (solo mientras no exista ninguna) */
    private function setup(): never
    {
        $data = $this->validate([
            'name'                  => 'required|max:120',
            'email'                 => 'required|email|max:190',
            'password'              => 'required|min:10|max:200',
            'password_confirmation' => 'required|same:password',
        ]);
        $id = Admin::register($data['name'], $data['email'], (string) $this->request->input('password'));
        AdminAuth::login(Admin::find($id));
        $this->redirect('/admin', 'Listo, creaste la cuenta de administración.');
    }

    public function logout(): void
    {
        AdminAuth::logout();
        $this->redirect('/admin/ingresar', 'Cerraste la sesión de administración.');
    }
}
