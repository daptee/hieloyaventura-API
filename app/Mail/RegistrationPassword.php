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
    public function __construct($email, $password)
    {
        // $this->messaged = "Su contraseña es <bold>$pass</bold>.";
        $this->messaged = "
        Le damos la bienvenida a Hielo y Aventura. <br>
        Se le ha generado una nueva cuenta, cuyos datos de acceso son: <br>
        usuario: $email <br>
        password: $password. <br><br> 
        
        Muchas gracias, el equipo de H&A.";

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
                    ->subject('Bienvenido a hielo y aventura')
                    ->view('emails.welcome')
                    ->with(["msg" => $this->messaged])
                    ;
    }
}
