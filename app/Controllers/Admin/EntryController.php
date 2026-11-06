<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\Session;
use App\Models\Entry;
use App\Models\EntryImage;
use App\Models\Section;
use App\Services\Uploader;
use RuntimeException;

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
        $this->saveGallery($id);
        $this->redirect('/admin/contenidos/' . $id, 'Entrega creada.');
    }

    public function edit(string $id): void
    {
        $this->view('admin/entries/form', [
            'title'    => 'Editar entrega',
            'entry'    => Entry::findOrFail($id),
            'gallery'  => EntryImage::forEntry((int) $id),
            'sections' => Section::all('sort ASC'),
        ]);
    }

    public function update(string $id): void
    {
        $entry = Entry::findOrFail($id);
        $data = $this->validated($entry);
        if ($data['title'] !== $entry['title'] || (int) $data['section_id'] !== (int) $entry['section_id']) {
            $data['slug'] = Entry::uniqueSlug((int) $data['section_id'], $data['title'], (int) $entry['id']);
        }
        Entry::update((int) $entry['id'], $data);
        $this->saveGallery((int) $entry['id']);
        $this->redirect('/admin/contenidos/' . $entry['id'], 'Entrega guardada.');
    }

    public function destroy(string $id): void
    {
        $entry = Entry::findOrFail($id);
        $images = EntryImage::forEntry((int) $entry['id']);
        Entry::delete((int) $entry['id']); // las filas de la galería se borran en cascada
        foreach ($images as $img) {
            $this->deleteIfUnused($img['path']);
        }
        $this->deleteIfUnused($entry['image']);
        $this->deleteIfUnused($entry['media_url']);
        $this->redirect('/admin/contenidos', 'Entrega borrada.');
    }

    /** "Insertar imagen en el texto": sube la imagen y devuelve el formato para pegar en el texto */
    public function uploadInline(): void
    {
        $file = $this->request->file('image');
        if (!$file) {
            $this->json(['error' => 'Elegí una imagen.'], 422);
        }
        try {
            $path = Uploader::store($file, 'image');
        } catch (RuntimeException $e) {
            $this->json(['error' => $e->getMessage()], 422);
        }
        $this->json(['url' => '/' . $path, 'markdown' => '![](' . $path . ')']);
    }

    /**
     * Valida el formulario y procesa los archivos.
     * Imagen: se sube un archivo (o se mantiene la actual). Video/audio/PDF: link o archivo subido.
     */
    private function validated(?array $existing = null): array
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
            'image_caption'=> 'nullable|max:200',
            'gallery_title'=> 'nullable|max:160',
            'body'         => 'nullable|max:60000',
            'media_url'    => 'nullable|max:500',
            'media_source' => 'nullable|in:link,file',
            'event_at'     => 'nullable|date',
            'published_at' => 'required|date',
        ]);
        if (!Section::find((int) $data['section_id'])) {
            $this->back('Elegí una sección.');
        }

        // Imagen
        $image = $existing['image'] ?? null;
        $newFiles = [];
        try {
            if ($file = $this->request->file('image_file')) {
                $newFiles[] = $path = Uploader::store($file, 'image');
                $image = $path;
            } elseif ($this->request->bool('image_remove')) {
                $image = null;
            }

            // Video, audio o PDF
            $media = $existing['media_url'] ?? null;
            if (($data['media_source'] ?? 'link') === 'file') {
                if ($file = $this->request->file('media_file')) {
                    $newFiles[] = $path = Uploader::store($file, 'media');
                    $media = '/' . $path;
                } elseif (!$media || !str_starts_with($media, '/uploads/')) {
                    throw new RuntimeException('Elegí el archivo de video, audio o PDF para subir.');
                }
            } else {
                $media = $data['media_url'] ?? null;
                if ($media && !preg_match('#^(https?://|/)#', $media)) {
                    throw new RuntimeException('El link tiene que empezar con https://');
                }
            }
            if ($this->request->bool('media_remove')) {
                $media = null;
            }
        } catch (RuntimeException $e) {
            array_map([Uploader::class, 'delete'], $newFiles);
            $old = $this->request->all();
            unset($old['_token']);
            Session::flash('errors', ['files' => [$e->getMessage()]]);
            Session::flash('old', $old);
            $this->back();
        }

        // Si se reemplazó o quitó un archivo subido, se borra del servidor
        if ($existing) {
            if ($existing['image'] !== $image) {
                $this->deleteIfUnused($existing['image'], (int) $existing['id']);
            }
            if ($existing['media_url'] !== $media) {
                $this->deleteIfUnused($existing['media_url'], (int) $existing['id']);
            }
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
            'image'        => $image,
            'image_caption'=> $data['image_caption'] ?? null,
            'gallery_title'=> $data['gallery_title'] ?? null,
            'body'         => $data['body'] ?? null,
            'media_url'    => $media,
            'event_at'     => !empty($data['event_at']) ? date('Y-m-d H:i:s', strtotime($data['event_at'])) : null,
            'published_at' => date('Y-m-d H:i:s', strtotime($data['published_at'])),
            'is_preview'   => $this->request->bool('is_preview') ? 1 : 0,
            'is_published' => $this->request->bool('is_published') ? 1 : 0,
        ];
    }

    /**
     * Galería: epígrafes, orden y bajas de las imágenes que ya están, más las imágenes nuevas.
     * La entrega ya quedó guardada; si una imagen nueva falla, se avisa y el resto se conserva.
     */
    private function saveGallery(int $entryId): void
    {
        $current = [];
        foreach (EntryImage::forEntry($entryId) as $img) {
            $current[(int) $img['id']] = $img;
        }

        $rows = (array) $this->request->input('gallery', []);
        foreach ($rows as $id => $row) {
            $id = (int) $id;
            if (!isset($current[$id]) || !is_array($row)) {
                continue;
            }
            if (!empty($row['remove'])) {
                EntryImage::delete($id);
                $this->deleteIfUnused($current[$id]['path'], $entryId);
                continue;
            }
            EntryImage::update($id, [
                'caption' => mb_substr_safe(trim((string) ($row['caption'] ?? '')), 200) ?: null,
                'sort'    => (int) ($row['sort'] ?? $current[$id]['sort']),
            ]);
        }

        $errors = [];
        foreach ($this->request->files('gallery_files') as $file) {
            try {
                EntryImage::add($entryId, Uploader::store($file, 'image'));
            } catch (RuntimeException $e) {
                $errors[] = $file['name'] . ': ' . $e->getMessage();
            }
        }
        if ($errors) {
            Session::flash('errors', ['files' => ['La entrega se guardó, pero algunas imágenes de la galería no se subieron. ' . implode(' · ', $errors)]]);
        }
    }

    /** Borra un archivo subido si ninguna otra entrega lo usa. */
    private function deleteIfUnused(?string $path, int $exceptId = 0): void
    {
        $path = ltrim((string) $path, '/');
        if (!str_starts_with($path, Uploader::BASE . '/')) {
            return;
        }
        $used = Database::value(
            'SELECT 1 FROM entries WHERE id <> ? AND (image = ? OR media_url = ? OR body LIKE ?) LIMIT 1',
            [$exceptId, $path, '/' . $path, '%' . $path . '%'],
        ) || Database::value('SELECT 1 FROM entry_images WHERE path = ? LIMIT 1', [$path]);
        if (!$used) {
            Uploader::delete($path);
        }
    }
}
