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
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($email, $pathPdf, $is_bigice, $hash_number, $reservation_number, $excurtion_name)
    {
        $this->email   = $email;
        $this->pathPdf = $pathPdf;
        $this->bigice = $is_bigice;
        $this->hash_reservation_number = $hash_number;
        $this->subject = "Ficha Medica - Nro reserva: $reservation_number - Hielo & Aventura";
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
                    ->subject($this->subject)
                    ->view('emails.user-reservation')
                    ->with(["bigice" => $this->bigice,
                        "hash_reservation_number" => $this->hash_reservation_number
                    ]);
    }
}
