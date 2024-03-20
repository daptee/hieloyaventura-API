<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MercadoPagoNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $payment_status, $payment_number, $order_number, $subject;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($payment_status, $payment_number, $order_number)
    {
        $this->subject = "Hielo y Aventura - Notificación Mercado Pago - Nro Reserva $order_number";
        $this->payment_status = $payment_status;
        $this->payment_number = $payment_number;
        $this->order_number = $order_number;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->subject)
                    ->view('emails.mercado-pago-notification');
    }
}
