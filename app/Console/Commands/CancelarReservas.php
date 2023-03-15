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
                    ->where('created_at', '<', now()->modify('-30 minute')->format('Y-m-d H:i:s'))
                    ->get();
        
        Log::debug("Cantidad de reservas que trae la query: " . count($reservations));

        if(count($reservations) > 0){
            foreach($reservations as $reservation){
                
                // $url = "https://apihya.hieloyaventura.com/apihya_dev/CancelaReservaM2";
                
                Log::debug("Numero de reserva: $reservation->reservation_number");
                
                $curl = curl_init();
                $fields = json_encode( array("RSV" => $reservation->reservation_number) );
                curl_setopt($curl, CURLOPT_URL, env("API_HYA")."/CancelaReservaM2");
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $fields);
                curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

                $resp = curl_exec($curl);
                curl_close($curl);

                Log::debug("Response: $resp");

                // echo json_decode($resp)->RESULT;

                // $fields = array('rsv' => $reservation->reservation_number);
                // $fields_string = http_build_query($fields);
                // $ch = curl_init();
                // curl_setopt($ch, CURLOPT_URL, env("API_HYA")."/CancelaReserva");
                // curl_setopt($ch, CURLOPT_POST, 1);
                // curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
                // $data = curl_exec($ch);
                // Log::debug($data['res']['data']['RESULT']);
                // Log::debug("Cronjob cancelar reserva: " . $data . " URL: " . env("API_HYA")."/CancelaReserva");
                // curl_close($ch);

                // $reservation->reservation_status_id = ReservationStatus::AUTOMATIC_CANCELED;
                // $reservation->save();
            }
        }
    }
}
