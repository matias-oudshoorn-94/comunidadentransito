<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Plan;

final class PlanController extends AdminController
{
    public function index(): void
    {
        $this->view('admin/plans', ['title' => 'Planes', 'plans' => Plan::all('sort ASC')]);
    }

    public function update(string $id): void
    {
        $plan = Plan::findOrFail($id);
        $data = $this->validate([
            'name'        => 'required|max:120',
            'price_ars'   => 'required|numeric',
            'price_usd'   => 'required|numeric',
            'description' => 'nullable|max:500',
        ]);
        Plan::update((int) $plan['id'], [
            'name'        => $data['name'],
            'price_ars'   => (float) $data['price_ars'],
            'price_usd'   => (float) $data['price_usd'],
            'description' => $data['description'] ?? null,
            'is_active'   => $this->request->bool('is_active') ? 1 : 0,
        ]);
        $this->back('Plan guardado. Los precios nuevos se aplican a las próximas compras.');
    }
}
