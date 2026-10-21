<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StoreUserWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $password;
    public $isPasswordUpdate;

    /**
     * Create a new message instance.
     *
     * $isPasswordUpdate = true when an existing store admin's password was
     * changed from the store edit form (changes subject + intro copy).
     */
    public function __construct($user, $password, $isPasswordUpdate = false)
    {
        $this->user = $user;
        $this->password = $password;
        $this->isPasswordUpdate = $isPasswordUpdate;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->isPasswordUpdate
                ? 'Your ' . config('app.name') . ' Store Password Has Been Updated'
                : 'Welcome to ' . config('app.name') . ' - Your Store Login Details',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.store.welcome',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
