<?php

namespace App\Console\Commands;

use App\Models\ReservationStatus;
use App\Models\UserReservation;
use App\Models\UserReservationStatusHistory;
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
        $reservations = UserReservation::where('reservation_status_id', [ReservationStatus::REJECTED, ReservationStatus::STARTED])
                                    ->where('created_at', '<', now()->modify('-30 minute')->format('Y-m-d H:i:s'))
                                    ->get();
        
        // Log::debug("Cantidad de reservas que trae la query: " . count($reservations));

        if(count($reservations) > 0){
            foreach($reservations as $reservation){
                
                // Log::debug("Numero de reserva: $reservation->reservation_number");
                
                $curl = curl_init();
                $fields = json_encode( array("RSV" => $reservation->reservation_number) );
                curl_setopt($curl, CURLOPT_URL, env("API_HYA")."/CancelaReservaM2");
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $fields);
                curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

                $resp = curl_exec($curl);
                curl_close($curl);

                // Log::debug("Response: $resp");

                if(!is_null($resp)){
                    if(json_decode($resp)->RESULT == "OK"){
                        $reservation->reservation_status_id = ReservationStatus::AUTOMATIC_CANCELED;
                        $reservation->save();

                        $user_reservation_status = new UserReservationStatusHistory();
                        $user_reservation_status->status_id = ReservationStatus::AUTOMATIC_CANCELED;
                        $user_reservation_status->user_reservation_id = $reservation->id;
                        $user_reservation_status->save();
                    }
                }

            }
        }
    }
}
