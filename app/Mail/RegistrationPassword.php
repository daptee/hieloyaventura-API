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

    public $messaged;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($pass)
    {
        $this->messaged = "Su contraseña es <bold>$pass</bold>.";
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('info@hieloyaventuras.com.ar', 'Hielo y aventuras')
                    // ->attach('/path/to/file')
                    ->replyTo('info@hieloyaventuras.com.ar')
                    ->subject('Contraseña generada')
                    ->view('emails.consults.consult')
                    ->with(["messaged" => $this->messaged])
                    ;
    }
}
