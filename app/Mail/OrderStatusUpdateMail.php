<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdateMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $notes;

    public function __construct(Order $order, $notes = null)
    {
        $this->order = $order->loadMissing('store', 'items');
        $this->notes = $notes;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Order Status Update - #' . $this->order->order_number . ' - ' . ucfirst($this->order->status),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.orders.status_update',
        );
    }
}
