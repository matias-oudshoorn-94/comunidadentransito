<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Plan;
use App\Models\Section;

final class HomeController extends Controller
{
    public function index(): void
    {
        $this->view('pages/home', [
            'title'    => null,
            'groups'   => Section::grouped(),
            'periods'  => Plan::byPeriod(),
            'faq'      => config('site.faq'),
        ]);
    }
}
