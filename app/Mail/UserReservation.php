<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserReservation extends Mailable
{
    // use Queueable, SerializesModels;
    public $subject = "";

    public $email;
    public $pathPdf;
    public $bigice;
    public $hash_reservation_number;
    public $msg;
    public $msg_is_bigice;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($email, $pathPdf, $is_bigice, $hash_number, $reservation_number, $excurtion_name, $language_id)
    {
        $this->email   = $email;
        $data_in_language = $this->get_data_in_language($language_id);
        $this->msg = $data_in_language['message'];
        $this->msg_is_bigice = $data_in_language['msg_is_bigice'];
        $this->pathPdf = $pathPdf;
        $this->bigice = $is_bigice;
        $this->hash_reservation_number = $hash_number;
        $this->subject = $data_in_language['subject'] . " " . $reservation_number;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('No-responder@hieloyaventura.com', 'Hielo & Aventura')
                    ->attach($this->pathPdf)
                    ->replyTo($this->email)
                    ->subject($this->subject)
                    ->view('emails.user-reservation')
                    ->with([
                        "msg" => $this->msg,
                        "msg_is_bigice" => $this->msg_is_bigice,
                        "bigice" => $this->bigice,
                        "hash_reservation_number" => $this->hash_reservation_number
                    ]);
    }

    public function get_data_in_language($language_id)
    {
        switch ($language_id) {
            case 2: // EN
                $message =  "Thank you for making your purchase with us. <br>
                Next we leave you attached a PDF with all the information of your reservation. Likewise, you can enter the web and with your username and password also download this PDF. <br> <br>
                If you have any inconvenience, you can write to us at info@hieloyaventura.com, or contact us at +54 (2902) 492 205/094";

                $msg_is_bigice = "Due to the fact that your excursion presents physical difficulties, we request that you please complete a medical file for all passengers, using the following link:";
                $subject = "Hielo & Aventura - Reservation number:";
                break;
            case 3: // PT
                $message =  "Obrigado por efetuar sua compra conosco. <br>
                De seguida deixamos-lhe em anexo um PDF com toda a informação da sua reserva. Da mesma forma, você pode entrar na web e com seu nome de usuário e senha também baixar este PDF. <br> <br>
                Se você tiver qualquer inconveniente, escreva para info@hieloyaventura.com ou entre em contato conosco pelo telefone +54 (2902) 492 205/094";

                $msg_is_bigice = "Devido ao fato de sua excursão apresentar dificuldades físicas, solicitamos que você preencha um arquivo médico para todos os passageiros, usando o seguinte link:";
                $subject = "Hielo & Aventura - Número da reserva:";
                break;
            default:
                $message =  "Gracias por realizar tu compra con nosotros. <br>
                A continuacion te dejamos adjunto un PDF con todos los datos de tu reserva. Asimismo, podes ingresar en la web y con tu usuario y contraseña descargar tambien este PDF. <br> <br>
                Si tenes algun inconveniente, podes escribirnos a info@hieloyaventura.com, o bien comunicarte con nosotros a +54 (2902) 492 205/094";

                $msg_is_bigice = "Debido a que tu excursion presenta dificultades fisicas, te solicitamos por favor que completes una ficha medica de todos los pasajeros, dentro del siguiente link:";
                $subject = "Hielo & Aventura - Reserva nro:";
                break;
        }

        return [ 'message' => $message, 'msg_is_bigice' => $msg_is_bigice, 'subject' => $subject ];
    }
}
