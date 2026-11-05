<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Plan;
use App\Models\Section;
use App\Services\AccessService;

final class HomeController extends Controller
{
    public function index(): void
    {
        // "Explorá lo que hay dentro": una sección de cada grupo
        $picks = [
            'pispeando-arte' => ['Pispeando Arte · ' . ucfirst(mes_nombre((int) date('n', strtotime('-1 month')))), 'La artista del mes: Dolores Mendieta y su atelier en San Javier, Córdoba.'],
            'capituleando'   => ['Capituleando', 'Un capítulo de sus libros, cada mes.'],
            'lugares'        => ['Lugares · ' . ucfirst(mes_nombre((int) date('n', strtotime('-1 month')))), 'Mainumbí: una pradera de flores, contada desde la emoción.'],
        ];
        $highlights = [];
        foreach ($picks as $slug => [$title, $text]) {
            if ($section = Section::bySlug($slug)) {
                $kicker = config('site.groups.' . $section['group_key']);
                $highlights[] = compact('section', 'title', 'text', 'kicker');
            }
        }

        $this->view('pages/home', [
            'title'      => null,
            'periods'    => Plan::byPeriod(),
            'faq'        => config('site.faq'),
            'highlights' => $highlights,
            'tiers'      => AccessService::tiersFor(Auth::user()),
        ]);
    }
}
