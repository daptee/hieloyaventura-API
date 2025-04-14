<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ReservationRequestChange extends Mailable
{
    use Queueable, SerializesModels;

    public $data, $reservation_number, $user;
    public $files;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data, $user, $files)
    {
        $this->data = $data;
        $this->reservation_number = $data['reservation_number'];
        $this->user = $user;
        $this->files = $files;
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

        $attachedFiles = $this->files;
        $email = $this->subject('Agencias - Solicitud de cambio reserva Nro: ' . $this->reservation_number)
        ->view('emails.reservation-request-change');

        foreach ($attachedFiles as $file) {
            $email->attach($file->getRealPath(), [
                'as' => $file->getClientOriginalName(),
                'mime' => $file->getMimeType(),
            ]);
        }

        return $email;
    }
}
