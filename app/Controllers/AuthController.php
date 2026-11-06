<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\User;

final class AuthController extends Controller
{
    /** C2 · Ingresar */
    public function loginForm(): void
    {
        $this->view('auth/login', ['title' => 'Ingresar']);
    }

    public function login(): void
    {
        $data = $this->validate(['email' => 'required|email', 'password' => 'required']);

        // Freno simple a intentos repetidos desde la misma sesión
        $attempts = (int) Session::get('login_attempts', 0);
        $lockedUntil = (int) Session::get('login_locked_until', 0);
        if ($lockedUntil > time()) {
            Session::flash('old', ['email' => $data['email']]);
            $this->back('Probaste muchas veces. Esperá un minuto y volvé a intentar.');
        }

        if (!Auth::attempt($data['email'], (string) $this->request->input('password'))) {
            Session::put('login_attempts', ++$attempts);
            if ($attempts >= 5) {
                Session::put('login_locked_until', time() + 60);
                Session::put('login_attempts', 0);
            }
            Session::flash('errors', ['email' => ['El email o la contraseña no coinciden.']]);
            Session::flash('old', ['email' => $data['email']]);
            $this->back();
        }
        Session::forget('login_attempts');

        $intended = Session::get('intended');
        Session::forget('intended');
        $this->redirect(is_string($intended) && str_starts_with($intended, '/') && !str_starts_with($intended, '/admin') ? $intended : '/mi-comunidad');
    }

    public function registerForm(): void
    {
        $this->view('auth/register', ['title' => 'Crear cuenta']);
    }

    public function register(): void
    {
        $data = $this->validate([
            'name'                  => 'required|max:120',
            'email'                 => 'required|email|max:190|unique:users,email',
            'password'              => 'required|min:8|max:200',
            'password_confirmation' => 'required|same:password',
        ]);
        $id = User::register($data['name'], $data['email'], (string) $this->request->input('password'));
        Auth::login(User::find($id));
        $this->redirect('/membresias', 'Te damos la bienvenida. Elegí tu membresía para entrar a todo.');
    }

    public function logout(): void
    {
        Auth::logout();
        $this->redirect('/', 'Cerraste sesión. ¡Hasta pronto!');
    }
}
