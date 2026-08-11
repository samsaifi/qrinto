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
    public function __construct(Order $order, bool $isAdmin = false, $pdfPath = null)
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

        if ($this->pdfPath) {
            $paths = is_array($this->pdfPath) ? $this->pdfPath : [$this->pdfPath];
            foreach ($paths as $idx => $path) {
                if ($path && file_exists($path)) {
                    $asName = count($paths) > 1 
                        ? 'Order_Design_' . $this->order->order_number . '_item_' . ($idx + 1) . '.pdf'
                        : 'Order_Design_' . $this->order->order_number . '.pdf';

                    $mail->attach($path, [
                        'as' => $asName,
                        'mime' => 'application/pdf',
                    ]);
                }
            }
        }

        return $mail;
    }
}
