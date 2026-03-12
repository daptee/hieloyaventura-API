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
    public $agency_name;
    public $reservation_name;
    public $number_of_passengers;
    public $source;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data, $request, $source = 'portal')
    {
        $this->data = $data;
        $this->request = $request;
        $this->source = $source;
        $this->agency_name = $request->agency_name ?? ($data->agency_name ?? null);
        // Determine reservation name (prefer request->pax for agency flow)
        $this->reservation_name = $request->pax ?? $request->reservation_name ?? ($data->contact_data->name ?? null);
        // Determine passenger count: prefer request->paxs_reservation if present
        if (isset($request->paxs_reservation) && is_array($request->paxs_reservation)) {
            $this->number_of_passengers = count($request->paxs_reservation);
        } elseif (isset($data->reservation_paxes) && is_array($data->reservation_paxes)) {
            $this->number_of_passengers = count($data->reservation_paxes);
        } elseif (isset($data->paxes) && is_array($data->paxes)) {
            $this->number_of_passengers = count($data->paxes);
        } else {
            $this->number_of_passengers = $request->number_of_passengers ?? null;
        }
        $this->data->meeting_point = $this->defineMeetingPoint($data);
        $this->turn = $request->turn ?? $data->turn->format('H:i\h\s');
        $this->subject = "Confirmacion reserva generada - Nro $data->reservation_number - Hielo & Aventura";
        /* $this->data = $data;
        $this->request = $request;
        if ($data->is_transfer == "true") { // Si tiene traslado
            $this->turn = isset($data->hotel_id) && $data->hotel_id == 225
                ? $data->turn->subMinutes(15)->format('H:i\h\s') // Con traslado pero SIN hotel (Oficina H&A)
                : $data->turn->format('H:i\h\s'); // Con traslado y hotel
        } else { // Sin traslado
            $this->turn = $data->turn->format('H:i\h\s');
        }
        $this->subject = "Confirmacion reserva generada - Nro $data->reservation_number - Hielo & Aventura"; */
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

    private function defineMeetingPoint($data)
    {
        if ($data->is_transfer) {
            if (empty($data->hotel_name) && $data->hotel_id == 225) {
                return 'Oficina H&A - Av. Libertador N°935';
            }
            return $data->hotel_name ?? '-';
        }

        return 'Puerto "Bajo de las Sombras"';
    }
}
