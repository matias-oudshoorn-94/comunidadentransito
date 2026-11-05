<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Section;

/** Textos de cada sección y qué niveles la abren */
final class SectionController extends AdminController
{
    public function index(): void
    {
        $this->view('admin/sections', ['title' => 'Secciones y acceso', 'groups' => Section::grouped()]);
    }

    public function update(string $id): void
    {
        $section = Section::findOrFail($id);
        $data = $this->validate([
            'name'   => 'required|max:120',
            'kicker' => 'nullable|max:80',
            'intro'  => 'nullable|max:1000',
        ]);
        $tiers = array_values(array_intersect(
            (array) $this->request->input('tiers', []),
            array_keys(config('site.tiers')),
        ));
        Section::update((int) $section['id'], [
            'name'   => $data['name'],
            'kicker' => $data['kicker'] ?? null,
            'intro'  => $data['intro'] ?? null,
        ]);
        Section::setTiers((int) $section['id'], $tiers);
        $this->back('Sección guardada.');
    }
}
