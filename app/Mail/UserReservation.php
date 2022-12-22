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

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($email, $pathPdf, $minitrekking_or_bigice, $hash_reservation_number, $reservation_number, $excurtion_name)
    {
        $this->email   = $email;
        $this->pathPdf = $pathPdf;
        $this->minitrekking_or_bigice = $minitrekking_or_bigice;
        $this->hash_reservation_number = $hash_reservation_number;
        $this->subject = "Reserva nro: $reservation_number - Excursion $excurtion_name";
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('No-responder@hieloyaventura.com', 'Hielo y Aventura')
                    ->attach($this->pathPdf)
                    ->replyTo('No-responder@hieloyaventura.com')
                    ->subject($this->subject)
                    ->view('emails.user-reservation')
                    ->with(["minitrekking_o_bigice" => $this->minitrekking_or_bigice,
                        "hash_reservation_number" => $this->hash_reservation_number
                    ]);
    }
}
