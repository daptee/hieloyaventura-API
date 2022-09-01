<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RegistrationPassword extends Mailable
{
    use Queueable, SerializesModels;
    public $subjet = "Contraseña generada";

    public $mensaje;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($pass)
    {
        $this->mensaje = "Su contraseña es <bold>$pass</bold>.";
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('info@hieloyaventuras.com.ar', 'Hielo y aventuras')
                    ->replyTo('info@hieloyaventuras.com.ar')
                    ->subject('Contraseña generada')
                    // ->view('mail.mailestados')
                    // ->with(["mensaje" => $this->mensaje])
                    ;
    }
}
