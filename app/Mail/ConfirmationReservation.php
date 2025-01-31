<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;

class ConfirmationReservation extends Mailable
{
    use Queueable, SerializesModels;

    public $data, $subject, $request, $turn;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data, $request)
    {
        /* $this->data = $data;
        $this->request = $request;
        $this->turn = $request->turn ?? $data->turn->format('H:i\h\s');     
        $this->subject = "Confirmacion reserva generada - Nro $data->reservation_number - Hielo & Aventura"; */
        $this->data = $data;
        $this->request = $request;
        if ($data->is_transfer == "true") { // Si tiene traslado
            $this->turn = isset($data->hotel_id) && $data->hotel_id == 225
                ? $data->turn->subMinutes(15)->format('H:i\h\s') // Con traslado pero SIN hotel (Oficina H&A)
                : $data->turn->format('H:i\h\s'); // Con traslado y hotel
        } else { // Sin traslado
            $this->turn = $data->turn->format('H:i\h\s');
        }
        $this->subject = "Confirmacion reserva generada - Nro $data->reservation_number - Hielo & Aventura";
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->subject)
            ->view('emails.confirmation-reservation');
    }
}
