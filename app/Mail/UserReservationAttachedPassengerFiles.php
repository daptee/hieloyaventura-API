<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserReservationAttachedPassengerFiles extends Mailable
{
    // use Queueable, SerializesModels;
    public $subject = "";
    public $msg = "";
    public $pathReservationZip;
    public $paxs;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($pathReservationZip, $reservation_number, $paxs)
    {
        $this->paxs = $paxs;
        $this->pathReservationZip = $pathReservationZip;
        $this->subject = "Hielo & Aventura - pasajeros reserva nro: $reservation_number";
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('No-responder@hieloyaventura.com', 'Hielo & Aventura')
                    ->attach($this->pathReservationZip)
                    ->subject($this->subject)
                    ->view('emails.user-reservation-attached-paxs-files')
                    ->with([
                        'paxs' => $this->paxs
                    ]);
    }

}
