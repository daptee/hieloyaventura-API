<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class MedicalRecordExternalMailable extends Mailable
{
    // use Queueable, SerializesModels;
    public $subject = "";

    public $email, $medical_record, $reservation_numb;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($email, $medical_record, $reservation_number)
    {
        $this->email = $email;
        $this->medical_record = $medical_record;
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
                    ->view('emails.external-medical-record')
                    ->with([ "medical_record" => $this->medical_record, 'reservation_numb' => $this->reservation_numb ]);
    }
}
