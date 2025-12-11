<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;

class NotificationErrorConfirmationInape extends Mailable
{
    use Queueable, SerializesModels;

    public $reservation_number, $subject, $attempt;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($reservation_number, $attempt = 1)
    {
        $this->reservation_number = $reservation_number;
        $this->attempt = $attempt;
        $this->subject = "Reserva Nro $reservation_number - Error en confirmacion (Intento #{$attempt})";
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->subject)
                    ->view('emails.notification-confirmation-error-inape');
    }
}
