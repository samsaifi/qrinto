<?php

namespace App\Mail;

use App\Models\Store;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent to the admin when a store registers itself from the public form.
 */
class StoreRegisteredAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public $store;
    public $user;

    public function __construct(Store $store, User $user)
    {
        $this->store = $store;
        $this->user = $user;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Store Registration: ' . $this->store->store_name . ' (' . $this->store->store_code . ')',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.store.registered-admin',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
