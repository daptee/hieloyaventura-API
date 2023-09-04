<?php

namespace App\Console\Commands;

use App\Mail\NotificacionPasajero;
use App\Models\ReservationStatus;
use App\Models\UserReservation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Exception;

// use Illuminate\Support\Facades\Http;

class NotificacionPasajeros extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notificar:pasajeros';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Este comando se encarga de notificar pasajeros';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $reservations_10_min = UserReservation::where('reservation_status_id', ReservationStatus::PAX_PENDING)->where('created_at', 'LIKE', '%'.now()->subMinutes(10)->format('Y-m-d H:i').':%')->get();

            $reservations_30_min = UserReservation::where('reservation_status_id', ReservationStatus::PAX_PENDING)->where('created_at', 'LIKE', '%'.now()->subMinutes(30)->format('Y-m-d H:i').':%')->get();

            $reservations_35_min = UserReservation::where('reservation_status_id', ReservationStatus::PAX_PENDING)->where('created_at', 'LIKE', '%'.now()->subMinutes(35)->format('Y-m-d H:i').':%')->get();

            if(count($reservations_10_min) > 0){
                foreach($reservations_10_min as $r_10_min){
                    $r_10_min_nombre = $r_10_min->user->name ?? null;
                    $r_10_min_nroReserva = $r_10_min->reservation_number;
                    $r_10_min_nombreExcursion = $r_10_min->excurtion->name;
                    $r_10_min_fechaExcursion = $r_10_min->date->format('d/m/Y');
                    $r_10_min_link = config('app.url_hya').'/mis-excursiones/' . Crypt::encryptString($r_10_min_nroReserva);
                    $r_10_min_email = $r_10_min->user->email;
                    $r_10_min_data = [
                        'email' => $r_10_min_email,
                        'subject' => "Hielo & Aventura - aviso carga de pasajeros - nro de reserva $r_10_min_nroReserva",
                        'msg' => "Hola $r_10_min_nombre. Enviamos este correo para notificarle que su compra de la excursion nro $r_10_min_nroReserva, $r_10_min_nombreExcursion, para el dia $r_10_min_fechaExcursion, aun no esta confirmada. Para ello, debe terminar de completar los datos de los pasajeros de la misma. Puede realizarlo desde el siguiente link: $r_10_min_link
                                
                                IMPORTANTE: Recuerde que si no completa estos datos, su reserva puede ser cancelada.
                                
                                Muchas gracias. El equipo de Hielo & Aventura."
                    ];
                    Mail::to($r_10_min_email)->send(new NotificacionPasajero($r_10_min_data));
                }
            }

            if(count($reservations_30_min) > 0){
                foreach($reservations_30_min as $r_30_min){
                    $r_30_min_nombre = $r_30_min->user->name ?? null;
                    $r_30_min_nroReserva = $r_30_min->reservation_number;
                    $r_30_min_nombreExcursion = $r_30_min->excurtion->name;
                    $r_30_min_fechaExcursion = $r_30_min->date;
                    $r_10_min_link = config('app.url_hya').'/mis-excursiones/' . Crypt::encryptString($r_30_min_nroReserva);
                    $r_30_min_email = $r_30_min->user->email;
                    $r_30_min_data = [
                        'email' => $r_30_min_email,
                        'subject' => "Hielo & Aventura - aviso carga de pasajeros - nro de reserva $r_30_min_nroReserva",
                        'msg' => "Hola $r_30_min_nombre, este mail es el ultimo aviso para que complete los datos de los pasajeros correspondiente a su reserva $r_30_min_nroReserva de la excursion $r_30_min_nombreExcursion, para el dia $r_30_min_fechaExcursion. Su compra será cancelada a la brevedad en caso de no competarse los datos solicitados. Puede realizarlo desde el siguiente link: $r_10_min_link
                                
                                Muchas gracias. El equipo de Hielo & Aventura."
                    ];
                    Mail::to($r_30_min_email)->send(new NotificacionPasajero($r_30_min_data));
                }
            }
            
            if(count($reservations_35_min) > 0){
                foreach($reservations_35_min as $r_35_min){
                    $r_35_min_nombre = $r_35_min->user->name ?? null;
                    $r_35_min_nroReserva = $r_35_min->reservation_number;
                    $r_35_min_email = $r_35_min->user->email;
                    $r_35_min_telefono = $r_35_min->user->phone ?? '-';
                    $r_35_min_data = [
                        'email' => $r_35_min_email,
                        'subject' => "Aviso de cancelación de reserva nro $r_35_min_nroReserva - falta de pasajeros",
                        'msg' => "Este es un aviso automatico proveniente de la web, para tomar decisiones sobre la compra con nro reserva $r_35_min_nroReserva, ya que se ha enviado los 2 avisos pero no se han completado los datos de los pasajeros.
                                
                                Los datos de contacto de esta reserva son:
                                
                                Nombre completo: $r_35_min_nombre
                                
                                Mail: $r_35_min_email
                                
                                Telefono: $r_35_min_telefono"
                    ];
                    Mail::to("online@hieloyaventura.com")->send(new NotificacionPasajero($r_35_min_data));
                }
            }
        } catch (Exception $error) {
            Log::debug( print_r(["Error al notificar a pasajero, detalle: " . $error->getMessage(), $error->getLine()], true));
        }
    }
}
