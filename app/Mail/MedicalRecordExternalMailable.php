<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class MedicalRecordExternalMailable extends Mailable
{
    // use Queueable, SerializesModels;
    public $subject = "";

    public $email, $passengers, $reservation_numb;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($email, $passengers_diseases, $reservation_number)
    {
        $this->email = $email;
        $this->passengers = $passengers_diseases;
        $this->reservation_numb = $reservation_number;
        $this->subject = "Ficha Medica Externa - Nro reserva: $reservation_number - Hielo & Aventura";
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('No-responder@hieloyaventura.com', 'Hielo & Aventura')
                    ->replyTo($this->email)
                    ->subject($this->subject)
                    ->view('emails.medical-record')
                    ->with([ "passengers" => $this->passengers, 'reservation_numb' => $this->reservation_numb ]);
    }
}
