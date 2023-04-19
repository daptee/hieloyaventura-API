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
        $reservations = UserReservation::whereIn('reservation_status_id', [ReservationStatus::REJECTED, ReservationStatus::STARTED])
                                    ->where('created_at', '<', now()->modify('-30 minute')->format('Y-m-d H:i:s'))
                                    ->where('reservation_number', '!=', 0)
                                    ->get();
        
        // Log::debug($reservations);
        Log::debug("Cantidad de reservas que trae la query: " . count($reservations));
        $url = config('app.api_hya')."/CancelaReservaM2";
        Log::debug("Url api: " . $url);

        if(count($reservations) > 0){
            foreach($reservations as $reservation){
                
                $curl = curl_init();
                $fields = json_encode( array("RSV" => $reservation->reservation_number) );
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $fields);
                curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

                $resp = curl_exec($curl);
                curl_close($curl);

                Log::debug("Respuesta API: $resp");

                $resultado = isset(json_decode($resp)->RESULT) ? json_decode($resp)->RESULT : "Sin resultado";
                $mensaje = isset(json_decode($resp)->ERROR_MSG) ? json_decode($resp)->ERROR_MSG : "Sin mensaje";

                Log::debug("Numero de reserva: $reservation->reservation_number , Resultado API: $resultado , MSG: $mensaje");

                if(isset(json_decode($resp)->RESULT)){
                    if(json_decode($resp)->RESULT == "OK" || json_decode($resp)->ERROR_MSG == "RSV:$reservation->reservation_number NO ENCONTRADA"){
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
