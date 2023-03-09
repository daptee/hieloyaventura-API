<?php

namespace App\Console\Commands;

use App\Models\ReservationStatus;
use App\Models\UserReservation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

// use Illuminate\Support\Facades\Http;

class CancelarReservas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cancelar:reservas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Este comando se encarga de cancelar reservas que quedaron pendientes (perdidas)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $reservations = UserReservation::where('reservation_status_id', ReservationStatus::STARTED)
        ->where('created_at', '<', now()->modify('-30 minute')->format('Y-m-d H:i:s'))->get();
        if(count($reservations) > 0){
            foreach($reservations as $reservation){
                $reservation->reservation_status_id = ReservationStatus::AUTOMATIC_CANCELED;
                $reservation->save();
                $fields = array('rsv' => $reservation->reservation_number);
                $fields_string = http_build_query($fields);
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, env("API_HYA")."/CancelaReserva");
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
                $data = curl_exec($ch);
                Log::debug("Cronjob cancelar reserva: " . $data);
                curl_close($ch);
            }
        }
    }
}
