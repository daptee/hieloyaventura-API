<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;

class ConfirmationReservation extends Mailable
{
    use Queueable, SerializesModels;

    public $data, $subject, $request;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data, $request)
    {
        $this->data = $data;
        $this->request = $request;
        $this->subject = "Confirmacion reserva generada - Nro $data->reservation_number - Hielo & Aventura";
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->subject)
                    ->view('emails.confirmation-reservation');
    }
}
