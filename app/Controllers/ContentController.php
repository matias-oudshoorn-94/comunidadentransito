<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Entry;
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
        $user = Auth::user();
        $this->view('content/index', [
            'title'  => 'Contenidos para vos',
            'groups' => Section::grouped(),
            'tiers'  => AccessService::tiersFor($user),
        ]);
    }

    /** B2 · Una sección: la primera entrega es de muestra, las demás con candado si no hay acceso */
    public function section(string $section): void
    {
        $sectionRow = Section::bySlug($section) ?? $this->abort(404);
        if ($sectionRow['slug'] === 'runas-de-letras') {
            $this->redirect('/contenidos/runas-de-letras');
        }
        $user = Auth::user();
        $this->view('content/section', [
            'title'     => $sectionRow['name'],
            'section'   => $sectionRow,
            'entries'   => Entry::forSection((int) $sectionRow['id']),
            'hasAccess' => AccessService::canAccessSection($user, $sectionRow),
            'groupName' => config('site.groups.' . $sectionRow['group_key']),
        ]);
    }

    /** B5 · Vista de una entrega */
    public function entry(string $section, string $entry): void
    {
        $sectionRow = Section::bySlug($section) ?? $this->abort(404);
        $entryRow = Entry::bySlug((int) $sectionRow['id'], $entry) ?? $this->abort(404);
        $user = Auth::user();

        if (!AccessService::canViewEntry($user, $sectionRow, $entryRow)) {
            $this->redirect('/membresias', 'Esta entrega es para miembros. Elegí tu plan para leerla completa.');
        }

        $siblings = array_values(array_filter(
            Entry::forSection((int) $sectionRow['id']),
            fn (array $e) => (int) $e['id'] !== (int) $entryRow['id'],
        ));

        $this->view('content/entry', [
            'title'     => $entryRow['title'],
            'section'   => $sectionRow,
            'entry'     => $entryRow,
            'hasAccess' => AccessService::canAccessSection($user, $sectionRow),
            'more'      => array_slice($siblings, 0, 3),
        ]);
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
        $this->view('content/runes', [
            'title'     => 'Runas de Letras',
            'section'   => $section,
            'hasAccess' => $hasAccess,
            'today'     => $hasAccess && $user ? Rune::drawnToday((int) $user['id']) : null,
        ]);
    }

    public function drawRune(): void
    {
        $section = Section::bySlug('runas-de-letras') ?? $this->abort(404);
        $user = Auth::user();
        if (!AccessService::canAccessSection($user, $section)) {
            $this->json(['error' => 'Las runas son para miembros.'], 403);
        }
        $rune = Rune::drawFor((int) $user['id']);
        if (!$rune) {
            $this->json(['error' => 'Todavía no hay runas cargadas.'], 404);
        }
        $this->json(['rune' => [
            'symbol'  => $rune['symbol'],
            'name'    => $rune['name'],
            'meaning' => $rune['meaning'],
            'prompt'  => $rune['prompt'],
        ]]);
    }

    /** Regalo de Tecniquitas: se deja el email y llega el material */
    public function gift(): void
    {
        $data = $this->validate(['email' => 'required|email|max:190']);
        Subscriber::add($data['email'], 'tecniquitas');
        Mailer::send(
            $data['email'],
            'Tu regalo de Tecniquitas',
            "¡Hola!\n\nAcá tenés tu regalo: " . absolute_url('/contenidos/tecniquitas') . "\n\nQue lo disfrutes,\nSil",
        );
        if ($this->request->wantsJson()) {
            $this->json(['message' => '¡Listo! Revisá tu email.']);
        }
        $this->redirect('/contenidos/tecniquitas', '¡Listo! Revisá tu email.');
    }
}
