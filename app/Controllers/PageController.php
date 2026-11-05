<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Book;
use App\Models\ContactMessage;
use App\Models\Plan;
use App\Models\Section;
use App\Services\Mailer;

final class PageController extends Controller
{
    public function community(): void
    {
        $this->view('pages/community', [
            'title'  => 'La Comunidad',
            'groups' => Section::grouped(),
        ]);
    }

    public function memberships(): void
    {
        $this->view('pages/memberships', [
            'title'   => 'Membresías',
            'periods' => Plan::byPeriod(),
            'groups'  => Section::grouped(),
            'faq'     => config('site.faq'),
        ]);
    }

    public function books(): void
    {
        $this->view('pages/books', [
            'title' => 'Libros',
            'books' => Book::all('sort ASC'),
        ]);
    }

    public function about(): void
    {
        $this->view('pages/about', ['title' => 'Sobre Silvina']);
    }

    public function contact(): void
    {
        $this->view('pages/contact', ['title' => 'Contacto']);
    }

    public function sendContact(): void
    {
        // Campo trampa para bots: queda oculto, una persona no lo completa
        if ($this->request->string('website') !== '') {
            $this->redirect('/contacto', 'Tu mensaje llegó. ¡Gracias por escribir!');
        }
        $data = $this->validate([
            'name'    => 'required|max:120',
            'email'   => 'required|email|max:190',
            'subject' => 'nullable|max:160',
            'message' => 'required|min:5|max:5000',
        ]);
        ContactMessage::create([
            'name'    => $data['name'],
            'email'   => strtolower($data['email']),
            'subject' => $data['subject'] ?? null,
            'message' => $data['message'],
        ]);
        Mailer::send(
            (string) config('site.social.email'),
            'Nuevo mensaje desde la web: ' . ($data['subject'] ?? $data['name']),
            "{$data['name']} <{$data['email']}> escribió:\n\n{$data['message']}",
        );
        $this->redirect('/contacto', 'Tu mensaje llegó. ¡Gracias por escribir!');
    }
}
