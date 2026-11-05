<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\ContactMessage;
use App\Models\Subscriber;

final class MessageController extends AdminController
{
    public function index(): void
    {
        $this->view('admin/messages', [
            'title'       => 'Mensajes',
            'messages'    => ContactMessage::all('created_at DESC'),
            'subscribers' => Subscriber::all('created_at DESC'),
        ]);
    }

    public function markRead(string $id): void
    {
        $message = ContactMessage::findOrFail($id);
        ContactMessage::update((int) $message['id'], ['read_at' => $message['read_at'] ? null : now()]);
        $this->back();
    }
}
