<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Entry;
use App\Models\Section;

final class EntryController extends AdminController
{
    public function index(): void
    {
        $sectionId = (int) $this->request->query('seccion', 0);
        $this->view('admin/entries/index', [
            'title'     => 'Contenidos',
            'entries'   => Entry::adminList($sectionId),
            'sections'  => Section::all('sort ASC'),
            'sectionId' => $sectionId,
        ]);
    }

    public function create(): void
    {
        $this->view('admin/entries/form', [
            'title'    => 'Nueva entrega',
            'entry'    => ['section_id' => (int) $this->request->query('seccion', 0), 'kind' => 'lectura', 'is_published' => 1, 'is_preview' => 0, 'published_at' => now()],
            'sections' => Section::all('sort ASC'),
        ]);
    }

    public function store(): void
    {
        $data = $this->validated();
        $data['slug'] = Entry::uniqueSlug((int) $data['section_id'], $data['title']);
        $id = Entry::create($data);
        $this->redirect('/admin/contenidos/' . $id, 'Entrega creada.');
    }

    public function edit(string $id): void
    {
        $this->view('admin/entries/form', [
            'title'    => 'Editar entrega',
            'entry'    => Entry::findOrFail($id),
            'sections' => Section::all('sort ASC'),
        ]);
    }

    public function update(string $id): void
    {
        $entry = Entry::findOrFail($id);
        $data = $this->validated();
        if ($data['title'] !== $entry['title'] || (int) $data['section_id'] !== (int) $entry['section_id']) {
            $data['slug'] = Entry::uniqueSlug((int) $data['section_id'], $data['title'], (int) $entry['id']);
        }
        Entry::update((int) $entry['id'], $data);
        $this->redirect('/admin/contenidos/' . $entry['id'], 'Entrega guardada.');
    }

    public function destroy(string $id): void
    {
        $entry = Entry::findOrFail($id);
        Entry::delete((int) $entry['id']);
        $this->redirect('/admin/contenidos', 'Entrega borrada.');
    }

    private function validated(): array
    {
        $data = $this->validate([
            'section_id'   => 'required|numeric',
            'title'        => 'required|max:200',
            'kind'         => 'required|in:' . implode(',', array_keys(Entry::KINDS)),
            'excerpt'      => 'nullable|max:400',
            'kicker'       => 'nullable|max:160',
            'meta'         => 'nullable|max:200',
            'hand_note'    => 'nullable|max:200',
            'category'     => 'nullable|max:60',
            'label'        => 'nullable|max:80',
            'image'        => 'nullable|max:200',
            'image_caption'=> 'nullable|max:200',
            'body'         => 'nullable|max:60000',
            'media_url'    => 'nullable|max:500',
            'event_at'     => 'nullable|date',
            'published_at' => 'required|date',
        ]);
        if (!Section::find((int) $data['section_id'])) {
            $this->back('Elegí una sección.');
        }
        $mediaUrl = $data['media_url'] ?? null;
        if ($mediaUrl && !preg_match('#^(https?://|/)#', $mediaUrl)) {
            $this->back('El link del archivo tiene que empezar con https:// o /');
        }
        return [
            'section_id'   => (int) $data['section_id'],
            'title'        => $data['title'],
            'kind'         => $data['kind'],
            'excerpt'      => $data['excerpt'] ?? null,
            'kicker'       => $data['kicker'] ?? null,
            'meta'         => $data['meta'] ?? null,
            'hand_note'    => $data['hand_note'] ?? null,
            'category'     => $data['category'] ?? null,
            'label'        => $data['label'] ?? null,
            'image'        => isset($data['image']) ? ltrim($data['image'], '/') : null,
            'image_caption'=> $data['image_caption'] ?? null,
            'body'         => $data['body'] ?? null,
            'media_url'    => $mediaUrl,
            'event_at'     => !empty($data['event_at']) ? date('Y-m-d H:i:s', strtotime($data['event_at'])) : null,
            'published_at' => date('Y-m-d H:i:s', strtotime($data['published_at'])),
            'is_preview'   => $this->request->bool('is_preview') ? 1 : 0,
            'is_published' => $this->request->bool('is_published') ? 1 : 0,
        ];
    }
}
