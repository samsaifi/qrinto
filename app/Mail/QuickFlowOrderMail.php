<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class QuickFlowOrderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $isAdmin;
    public $pdfPath;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order, bool $isAdmin = false, string $pdfPath = null)
    {
        $this->order = $order->loadMissing('store', 'items');
        $this->isAdmin = $isAdmin;
        $this->pdfPath = $pdfPath;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subject = $this->isAdmin 
            ? 'New Quick Flow Order Received: #' . $this->order->order_number 
            : 'Your Qrinto Order Confirmation: #' . $this->order->order_number;

        $mail = $this->subject($subject)
            ->view('emails.quick-flow-order');

        if ($this->pdfPath && file_exists($this->pdfPath)) {
            $mail->attach($this->pdfPath, [
                'as' => 'Order_Design_' . $this->order->order_number . '.pdf',
                'mime' => 'application/pdf',
            ]);
        }

        return $mail;
    }
}
