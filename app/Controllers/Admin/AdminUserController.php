<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminAuth;
use App\Models\Admin;

/** Administradores: alta, edición y baja de las cuentas que entran a /admin */
final class AdminUserController extends AdminController
{
    public function index(): void
    {
        $this->view('admin/admins', [
            'title'  => 'Administradores',
            'admins' => Admin::all('name ASC'),
            'me'     => AdminAuth::id(),
        ]);
    }

    public function store(): void
    {
        $data = $this->validate([
            'name'     => 'required|max:120',
            'email'    => 'required|email|max:190|unique:admins,email',
            'password' => 'required|min:10|max:200',
        ]);
        Admin::register($data['name'], $data['email'], (string) $this->request->input('password'));
        $this->redirect('/admin/administradores', 'Agregaste a ' . $data['name'] . '. Ya puede ingresar en /admin/ingresar.');
    }

    public function update(string $id): void
    {
        $admin = Admin::findOrFail($id);
        $data = $this->validate([
            'name'     => 'required|max:120',
            'email'    => 'required|email|max:190|unique:admins,email,' . $admin['id'],
            'password' => 'nullable|min:10|max:200',
        ]);
        $update = ['name' => $data['name'], 'email' => strtolower($data['email'])];
        if (!empty($data['password'])) {
            $update['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        Admin::update((int) $admin['id'], $update);
        AdminAuth::refresh();
        $this->redirect('/admin/administradores', 'Datos guardados.');
    }

    public function destroy(string $id): void
    {
        $admin = Admin::findOrFail($id);
        if ((int) $admin['id'] === AdminAuth::id()) {
            $this->back('No podés quitarte a vos. Pedile a otra persona administradora que lo haga.');
        }
        if (Admin::count() <= 1) {
            $this->back('Tiene que quedar al menos una cuenta de administración.');
        }
        Admin::delete((int) $admin['id']);
        $this->redirect('/admin/administradores', 'Quitaste el acceso de ' . $admin['name'] . '.');
    }
}
