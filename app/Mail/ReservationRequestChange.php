<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;

class ReservationRequestChange extends Mailable
{
    use Queueable, SerializesModels;

    public $data, $reservation_number, $user_name;
    public $attachment;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data, $user_name, $attachment = null)
    {
        $this->data = $data;
        $this->reservation_number = $data['reservation_number'];
        $this->user_name = $user_name;
        $this->attachment = $attachment;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // return $this->subject('Agencias - Solicitud de cambio reserva Nro: ' . $this->reservation_number)
        //             ->view('emails.reservation-request-change');

        $email = $this->subject('Agencias - Solicitud de cambio reserva Nro: ' . $this->reservation_number)
        ->view('emails.reservation-request-change');

        // Adjuntar el archivo si está presente
        if ($this->attachment) {
            $email->attach($this->attachment->getRealPath(), [
            'as' => $this->attachment->getClientOriginalName(),
            'mime' => $this->attachment->getMimeType(),
            ]);
        }

        return $email;
    }
}
