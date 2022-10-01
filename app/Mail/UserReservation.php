<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserReservation extends Mailable
{
    // use Queueable, SerializesModels;
    public $subjet = "Reserva creada exitosamente";

    public $email;
    public $pathPdf;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($email, $pathPdf)
    {
        $this->email   = $email;
        $this->pathPdf = $pathPdf;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('info@hieloyaventuras.com.ar', 'Hielo y Aventura')
                    ->attach($this->pathPdf)
                    ->replyTo('info@hieloyaventuras.com.ar')
                    ->subject($this->subject)
                    ->view('emails.user-reservation')
                    ->with(["msg" => "El pago fue exitoso."])
                    ;
    }
}
