<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;

abstract class AdminController extends Controller
{
    protected string $layout = 'layouts/admin';
}
