<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;
use App\Models\Entry;
use App\Models\EntryImage;
use App\Models\Rune;
use App\Models\Section;
use App\Models\Subscriber;
use App\Services\AccessService;
use App\Services\Mailer;

final class ContentController extends Controller
{
    /** B1 · Contenidos para vos */
    public function index(): void
    {
        $this->view('content/index', [
            'title'     => 'Contenidos para vos',
            'groups'    => Section::grouped(),
            'tiers'     => AccessService::tiersFor(Auth::user()),
            'fromPrice' => precio_desde(),
        ]);
    }

    /** B2 · Una sección: la entrega de muestra se abre; las demás piden membresía */
    public function section(string $section): void
    {
        $sectionRow = Section::bySlug($section) ?? $this->abort(404);
        $this->view('content/section', [
            'title'     => $sectionRow['name'],
            'section'   => $sectionRow,
            'cfg'       => config('sections.' . $sectionRow['slug'], []),
            'entries'   => Entry::forSection((int) $sectionRow['id']),
            'hasAccess' => AccessService::canAccessSection(Auth::user(), $sectionRow),
            'groupName' => config('site.groups.' . $sectionRow['group_key']),
        ]);
    }

    /** B5 · Una entrega. Sin acceso se ve el comienzo y el aviso para sumarse. */
    public function entry(string $section, string $entry): void
    {
        $sectionRow = Section::bySlug($section) ?? $this->abort(404);
        $entryRow = Entry::bySlug((int) $sectionRow['id'], $entry) ?? $this->abort(404);
        $user = Auth::user();
        $hasAccess = AccessService::canAccessSection($user, $sectionRow);

        // Anterior / siguiente dentro de la sección (por fecha)
        $siblings = Database::all(
            'SELECT slug, title, published_at FROM entries WHERE section_id = ? AND is_published = 1 AND published_at <= ? ORDER BY published_at ASC, id ASC',
            [$sectionRow['id'], now()],
        );
        $index = array_search($entryRow['slug'], array_column($siblings, 'slug'), true);
        $prev = $index !== false && $index > 0 ? $siblings[$index - 1] : null;
        $next = $index !== false && $index < count($siblings) - 1 ? $siblings[$index + 1] : null;

        $canRead = AccessService::canViewEntry($user, $sectionRow, $entryRow);
        $this->view('content/entry', [
            'title'     => $entryRow['title'],
            'section'   => $sectionRow,
            'entry'     => $entryRow,
            'cfg'       => config('sections.' . $sectionRow['slug'], []),
            'hasAccess' => $hasAccess,
            'canRead'   => $canRead,
            'gallery'   => $canRead ? EntryImage::forEntry((int) $entryRow['id']) : [],
            'prev'      => $prev,
            'next'      => $next,
            'more'      => $this->moreForMonth((int) $sectionRow['id']),
        ]);
    }

    /** "Más para este mes": lo último de otras secciones, más la runa del día */
    private function moreForMonth(int $exceptSectionId): array
    {
        $items = [];
        foreach (['lugares', 'runas-de-letras', 'capituleando'] as $slug) {
            $s = Section::bySlug($slug);
            if (!$s || (int) $s['id'] === $exceptSectionId) {
                continue;
            }
            if ($slug === 'runas-de-letras') {
                $items[] = ['url' => '/contenidos/runas-de-letras', 'image' => 'fotos/runa-dibujo.jpg', 'kicker' => 'RUNAS DE LETRAS',
                    'title' => 'Tu runa de hoy', 'text' => 'Sacá una runa y dejá que te inspire.', 'link' => 'Sacar una'];
                continue;
            }
            $e = Entry::forSection((int) $s['id'])[0] ?? null;
            if ($e) {
                $items[] = ['url' => "/contenidos/{$slug}/{$e['slug']}", 'image' => $e['image'], 'kicker' => $s['name'],
                    'title' => $slug === 'capituleando' ? 'Capítulo de regalo' : explode(',', $e['title'])[0],
                    'text' => excerpt($e['excerpt'], 60), 'link' => $slug === 'capituleando' ? 'Descargar' : 'Leer'];
            }
        }
        return $items;
    }

    /** "Agendar": descarga un .ics para agregar el encuentro al calendario */
    public function calendar(string $section, string $entry): void
    {
        $sectionRow = Section::bySlug($section) ?? $this->abort(404);
        $entryRow = Entry::bySlug((int) $sectionRow['id'], $entry) ?? $this->abort(404);
        if (!$entryRow['event_at'] || !AccessService::canViewEntry(Auth::user(), $sectionRow, $entryRow)) {
            $this->abort(404);
        }
        $start = strtotime((string) $entryRow['event_at']);
        $fmt = fn (int $ts) => gmdate('Ymd\THis\Z', $ts);
        $escape = fn (string $s) => addcslashes($s, ",;\\");
        $ics = implode("\r\n", [
            'BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//Comunidad en Transito//ES', 'CALSCALE:GREGORIAN',
            'BEGIN:VEVENT',
            'UID:entrega-' . $entryRow['id'] . '@comunidadentransito',
            'DTSTAMP:' . $fmt(time()),
            'DTSTART:' . $fmt($start),
            'DTEND:' . $fmt($start + 90 * 60),
            'SUMMARY:' . $escape($entryRow['title'] . ' · ' . config('app.name')),
            'DESCRIPTION:' . $escape(absolute_url("/contenidos/{$section}/{$entry}")),
            'END:VEVENT', 'END:VCALENDAR', '',
        ]);
        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $entryRow['slug'] . '.ics"');
        echo $ics;
    }

    /** B4 · Runas de Letras */
    public function runes(): void
    {
        $section = Section::bySlug('runas-de-letras') ?? $this->abort(404);
        $user = Auth::user();
        $hasAccess = AccessService::canAccessSection($user, $section);
        $today = $hasAccess && $user ? Rune::drawnToday((int) $user['id']) : null;
        $this->view('content/runes', [
            'title'     => 'Runas de Letras',
            'section'   => $section,
            'hasAccess' => $hasAccess,
            'today'     => $today,
            'giftUsed'  => (bool) Session::get('rune_gift'),
        ]);
    }

    /** Miembros: una runa por día. Visitantes: la primera es de regalo (una por sesión). */
    public function drawRune(): void
    {
        $section = Section::bySlug('runas-de-letras') ?? $this->abort(404);
        $user = Auth::user();
        $member = $user && AccessService::canAccessSection($user, $section);

        if ($member) {
            $rune = Rune::drawFor((int) $user['id']);
        } elseif (Session::get('rune_gift')) {
            $this->json(['error' => 'Tu runa de regalo ya salió. Sumate para sacar una cada día.'], 403);
        } else {
            $ids = Database::column('SELECT id FROM runes WHERE is_active = 1');
            $rune = $ids ? Rune::find((int) $ids[random_int(0, count($ids) - 1)]) : null;
            Session::put('rune_gift', true);
        }
        if (empty($rune)) {
            $this->json(['error' => 'Todavía no hay runas cargadas.'], 404);
        }
        $this->json([
            'gift' => !$member,
            'rune' => ['symbol' => $rune['symbol'], 'name' => $rune['name'], 'meaning' => $rune['meaning'], 'prompt' => $rune['prompt']],
        ]);
    }

    /** Regalo de Tecniquitas: se deja el email y llega el material */
    public function gift(): void
    {
        $data = $this->validate(['email' => 'required|email|max:190']);
        Subscriber::add($data['email'], 'tecniquitas');
        Mailer::send(
            $data['email'],
            'Tu Tecniquita de regalo',
            "¡Hola!\n\nAcá tenés tu Tecniquita de regalo: " . absolute_url('/contenidos/tecniquitas') . "\n\nQue la disfrutes,\nSilvina",
        );
        if ($this->request->wantsJson()) {
            $this->json(['message' => '¡Listo! Revisá tu email.']);
        }
        $this->back('¡Listo! Revisá tu email.');
    }
}
