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
                    try {
                        //code...
                        $r_10_min_nombre = $r_10_min->user->name ?? null;
                        $r_10_min_nroReserva = $r_10_min->reservation_number;
                        $r_10_min_nombreExcursion = $r_10_min->excurtion->name;
                        $r_10_min_fechaExcursion = $r_10_min->date->format('d/m/Y');
                        $r_10_min_link = config('app.url_hya').'/mis-excursiones/' . Crypt::encryptString($r_10_min_nroReserva);
                        $r_10_min_email = $r_10_min->user->email;
                        $array_data_function_language = [
                            'nombre' => $r_10_min_nombre,
                            'nroReserva' => $r_10_min_nroReserva,
                            'nombreExcursion' => $r_10_min_nombreExcursion,
                            'fechaExcursion' => $r_10_min_fechaExcursion,
                            'link' => $r_10_min_link
                        ];
                        $r_10_min_data = [
                            'email' => $r_10_min_email,
                            'subject' => $this->get_data_language_r_10($r_10_min->language_id, $array_data_function_language)['subject'] . $r_10_min_nroReserva,
                            'msg' => $this->get_data_language_r_10($r_10_min->language_id, $array_data_function_language)['msg'],
                        ];
                        Mail::to($r_10_min_email)->send(new NotificacionPasajero($r_10_min_data));
                    } catch (Exception $error) {
                        Log::debug( print_r(["Error al notificar a pasajero (10 min), detalle: " . $error->getMessage() . " nro reserva: $r_10_min->reservation_number", $error->getLine()], true));
                    }
                }
            }

            if(count($reservations_30_min) > 0){
                foreach($reservations_30_min as $r_30_min){
                    try {
                        $r_30_min_nombre = $r_30_min->user->name ?? null;
                        $r_30_min_nroReserva = $r_30_min->reservation_number;
                        $r_30_min_nombreExcursion = $r_30_min->excurtion->name;
                        $r_30_min_fechaExcursion = $r_30_min->date;
                        $r_30_min_link = config('app.url_hya').'/mis-excursiones/' . Crypt::encryptString($r_30_min_nroReserva);
                        $r_30_min_email = $r_30_min->user->email;
                        $array_data_function_language = [
                            'nombre' => $r_30_min_nombre,
                            'nroReserva' => $r_30_min_nroReserva,
                            'nombreExcursion' => $r_30_min_nombreExcursion,
                            'fechaExcursion' => $r_30_min_fechaExcursion,
                            'link' => $r_30_min_link
                        ];
                        $r_30_min_data = [
                            'email' => $r_30_min_email,
                            'subject' => $this->get_data_language_r_30($r_30_min->language_id, $array_data_function_language)['subject'] . $r_30_min_nroReserva,
                            'msg' => $this->get_data_language_r_30($r_30_min->language_id, $array_data_function_language)['msg'],
                        ];
                        Mail::to($r_30_min_email)->send(new NotificacionPasajero($r_30_min_data));
                    } catch (Exception $error) {
                        Log::debug( print_r(["Error al notificar a pasajero (30 min), detalle: " . $error->getMessage() . " nro reserva: $r_30_min->reservation_number", $error->getLine()], true));
                    }
                }
            }
            
            if(count($reservations_35_min) > 0){
                foreach($reservations_35_min as $r_35_min){
                    try {
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
                    } catch (Exception $error) {
                        Log::debug( print_r(["Error al notificar a pasajero (35 min), detalle: " . $error->getMessage() . " nro reserva: $r_35_min->reservation_number", $error->getLine()], true));
                    }
                }
            }
        } catch (Exception $error) {
            Log::debug( print_r(["Error al notificar a pasajero (general), detalle: " . $error->getMessage(), $error->getLine()], true));
        }
    }

    public function get_data_language_r_10($language_id, $array_data)
    {
        $nombre = $array_data["nombre"];
        $nroReserva = $array_data["nroReserva"];
        $nombreExcursion = $array_data["nombreExcursion"];
        $fechaExcursion = $array_data["fechaExcursion"];
        $link = $array_data["link"];

        switch ($language_id) {
            case 1: // español 
                $subject = "Hielo & Aventura - aviso carga de pasajeros - nro de reserva ";
                $msg = "Hola $nombre. Enviamos este correo para notificarle que su compra de la excursion nro $nroReserva, $nombreExcursion, para el dia $fechaExcursion, aun no esta confirmada. Para ello, debe terminar de completar los datos de los pasajeros de la misma. Puede realizarlo desde el siguiente link: $link
                                    
                IMPORTANTE: Recuerde que si no completa estos datos, su reserva puede ser cancelada.
                
                Muchas gracias. El equipo de Hielo & Aventura.";
                break;
            
            case 2: // ingles
                $subject = "Hielo & Aventura - passenger load notice - reservation number ";
                $msg = "Hello $nombre. We are sending this email to notify you that your purchase of excursion number $nroReserva, $nombreExcursion, for the day $fechaExcursion, is not yet confirmed. To do this, you must finish completing the information of the passengers of the same. You can do it from the following link: $link
                                    
                IMPORTANT: Remember that if you do not complete these details, your reservation may be cancelled.
               
                Thank you so much. Hielo & Aventura team.";
                break;
            
            case 3: // portugues
                $subject = "Hielo & Aventura - aviso de carga de passageiros - número de reserva";
                $msg = "Olá $nombre. Enviamos este e-mail para avisar que sua compra da excursão número $nroReserva, $nombreExcursion, para o dia $fechaExcursion, ainda não está confirmada. Para isso, você deve terminar de preencher os dados dos passageiros do o mesmo. Você pode fazer isso no seguinte link: $link
                                    
                IMPORTANTE: Lembre-se que se você não preencher estes dados, sua reserva poderá ser cancelada.
               
                Muito obrigado. A equipe Hielo & Aventura.";
                break;

            default: //
                # code...
                break;
        }

        return [
            'subject' => $subject,
            'msg' => $msg
        ];
    }

    public function get_data_language_r_30($language_id, $array_data)
    {
        $nombre = $array_data["nombre"];
        $nroReserva = $array_data["nroReserva"];
        $nombreExcursion = $array_data["nombreExcursion"];
        $fechaExcursion = $array_data["fechaExcursion"];
        $link = $array_data["link"];

        switch ($language_id) {
            case 1: // español 
                $subject = "Hielo & Aventura - aviso carga de pasajeros - nro de reserva ";
                $msg = "Hola $nombre, este mail es el ultimo aviso para que complete los datos de los pasajeros correspondiente a su reserva $nroReserva de la excursion $nombreExcursion, para el dia $fechaExcursion. Su compra será cancelada a la brevedad en caso de no competarse los datos solicitados. Puede realizarlo desde el siguiente link: $link
                                    
                Muchas gracias. El equipo de Hielo & Aventura.";
                break;
            
            case 2: // ingles
                $subject = "Hielo & Aventura - passenger load notice - reservation number ";
                $msg = "Hello $nombre, this email is the last notice for you to complete the passenger data corresponding to your reservation $nroReserva of the $nombreExcursion, for the day $fechaExcursion. Your purchase will be canceled as soon as possible if the requested information is not completed. You can do it from the following link: $link 
                                    
                Thank you so much. Hielo & Aventura team.";
                break;
            
            case 3: // portugues
                $subject = "Hielo & Aventura - aviso de carga de passageiros - número de reserva";
                $msg = "Olá $nombre. este e-mail é o último aviso para você completar os dados do passageiro correspondente à sua reserva $nroReserva da excursão $nombreExcursion, para o dia $fechaExcursion, ainda não está confirmada. Para isso, você deve terminar de preencher os dados dos passageiros do o mesmo. Você pode fazer isso no seguinte link: $link
                                    
                Muito obrigado. A equipe Hielo & Aventura.";
                break;

            default: //
                # code...
                break;
        }

        return [
            'subject' => $subject,
            'msg' => $msg
        ];
    }
}
