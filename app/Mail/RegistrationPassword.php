<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Tymon\JWTAuth\Claims\Subject;

class RegistrationPassword extends Mailable
{
    use Queueable, SerializesModels;

    public $msg, $subject;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($email, $password, $language_id)
    {
        $data_in_language = $this->get_data_in_language($email, $password, $language_id);
        $this->msg = $data_in_language['message'];
        $this->subject = $data_in_language['subject'];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('No-responder@hieloyaventura.com', 'Hielo & Aventura')
                    ->replyTo('No-responder@hieloyaventura.com')
                    ->subject($this->subject)
                    ->view('emails.welcome')
                    ->with(["msg" => $this->msg])
                    ;
    }

    public function get_data_in_language($email, $password ,$language_id)
    {
        switch ($language_id) {
            case 2: // EN
                $message = "
                We welcome you to Hielo & Aventura. <br><br>
                A new account has been generated for you, whose access data is: <br>
                user: $email <br>
                password: $password <br><br> 
                
                Thank you very much, the H&A team.";

                $subject = "Welcome to Hielo & Aventura";
                break;
            case 3: // PT
                $message = "
                Sejam bem-vindos ao Hielo & Aventura. <br><br>
                Uma nova conta foi gerada para você, cujos dados de acesso são: <br>
                usuário: $email <br>
                senha: $password <br><br> 
                
                Muito obrigado, equipe H&A.";
                
                $subject = "Bem-vindo ao Hielo & Aventura";
                break;
            default:
                $message = "
                Le damos la bienvenida a Hielo & Aventura. <br><br>
                Se le ha generado una nueva cuenta, cuyos datos de acceso son: <br>
                usuario: $email <br>
                contraseña: $password <br><br> 
                
                Muchas gracias, el equipo de H&A.";

                $subject = "Bienvenido a Hielo & Aventura";
                break;
        }

        return [ 'message' => $message, 'subject' => $subject ];
    }
}
