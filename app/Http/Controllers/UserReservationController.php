<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserReservationRequest;
use App\Http\Requests\UpdateUserReservationRequest;
use App\Mail\RegistrationPassword;
use App\Mail\UserReservation as MailUserReservation;
use App\Models\BillingDataReservation;
use App\Models\ContactDataReservation;
use App\Models\Pax;
use App\Models\RejectedReservation;
use App\Models\ReservationPax;
use App\Models\ReservationStatus;
use App\Models\User;
use App\Models\UserReservation;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PDF;
use setasign\Fpdi\Fpdi;

class UserReservationController extends Controller
{
    public $model = UserReservation::class;
    public $s = "reserva"; //sustantivo singular
    public $sp = "reservas"; //sustantivo plural
    public $ss = "reserva/s"; //sustantivo sigular/plural
    public $v = "a"; //verbo ej:encontrado/a
    public $pr = "la"; //preposicion singular
    public $prp = "las"; //preposicion plural

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $message = "Error al traer listado de {$this->sp}.";
        try {
            $data = $this->model::with($this->model::INDEX);
            foreach ($request->all() as $key => $value) {
                if (method_exists($this->model, 'scope' . $key)) {
                    $data->$key($value);
                }
            }
            $data = $data->where('user_id', auth()->user()->id);
            $data = $data->get();
        } catch (ModelNotFoundException $error) {
            return response(["message" => "No se encontraron " . $this->sp . "."], 404);
        } catch (Exception $error) {
            return response(["message" => $message, "error" => $error->getMessage()], 500);
        }
        $message = ucfirst($this->sp) . " encontrad{$this->v}s exitosamente.";
        return response(compact("message", "data"));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreUserReservationRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUserReservationRequest $request)
    {
        $message = "Error al crear en la {$this->s}.";
        $datos = $request->all();

        try {
            DB::beginTransaction();
                // Crear un usuario si se manda "create_user" en true
                $user = User::where('email', $datos['contact_data']['email'])->first();
                    if ($datos['create_user'] and isset($datos['contact_data']) and !$user) {
                        $pass = Str::random(8);
                        $passHashed = Hash::make($pass);
                        $user = User::createUser($datos['contact_data'] + [
                            'password' => $passHashed,
                        ]);
                        //Email de Bienvenida
                            try {
                                Mail::to($datos['contact_data']['email'])->send(new RegistrationPassword($datos['contact_data']['email'], $pass));
                            } catch (\Throwable $th) {
                                Log::debug(print_r([$th->getMessage(), $th->getLine()],  true));
                            }
                        //
                        //Buscar los user_reservations donde el user_id sea NULL y el contact_data (realicion en la otra tabla) tiene el email del $datos['contact_data']['email'] 
                        //Si se encuentra, ponerle a todos esos user_reservations, en el user_id, el id del nuevo usuario creado ($user->id)
                            // ...
                        //
                    }
                //
                
                //Creo el registro en user_reservations
                    $newUserReservation = new $this->model($datos + ["reservation_status_id" => ReservationStatus::STARTED]);
                    $newUserReservation->user_id = $datos['user_id'] ?? (isset($user) ? $user->id : null);
                    $newUserReservation->save();
                //

                //Creo los registros de los pasajeros en paxes
                    if (isset($datos['paxs'])) {
                        foreach ($datos['paxs'] as $pax) {
                            Pax::create($pax + ['user_reservation_id' => $newUserReservation->id]);
                        }
                    }
                //
                //Creo los registros de los pasajeros en reservation_paxes
                    if (isset($datos['paxs_reservation'])) {
                        foreach ($datos['paxs_reservation'] as $paxs) {
                            ReservationPax::create($paxs + ['user_reservation_id' => $newUserReservation->id]);
                        }
                    }
                //
                    //Pasar el last name como nullable porque en el name le pasará el fullname y el lastname se completará en null o vacio
                //biling data reservation
                    if(isset($datos['billing_data'])) {
                        BillingDataReservation::create($datos['billing_data'] + ['user_reservation_id' => $newUserReservation->id]);
                    }
                //contact data reservation
                    if(isset($datos['contact_data'])) {
                        ContactDataReservation::create($datos['contact_data'] + ['user_reservation_id' => $newUserReservation->id]);
                    }
                //
                
            DB::commit();
        } catch (ModelNotFoundException $error) {
            DB::rollBack();
            return response(["message" => "No se encontraron {$this->prp} {$this->sp}.", "error" => $error->getMessage()], 404);
        } catch (Exception $error) {
            DB::rollBack();
            Log::debug( print_r([$error->getMessage(), $error->getLine()], true));
            return response(["message" => $message, "error" => "URC0001"], 500);
        }

        $message = "Se ha creado {$this->pr} {$this->s} correctamente.";
        $newUserReservation = $this->model::with($this->model::SHOW)->findOrFail($newUserReservation->id);
        //Mandar email con el PDF adjunto
            try {
                Carbon::setLocale('es');
                $date = $newUserReservation->date;
                $dateFormated = $date->translatedFormat('l j \d\e F');

                Mail::to($datos['contact_data']['email'])->send(
                    new MailUserReservation($datos['contact_data']['email'],
                                            $this->createPdf(
                                                public_path("Hoja 2 - vacia.pdf"), 
                                                $newUserReservation->reservation_number, 
                                                $newUserReservation->contact_data->name, 
                                                $newUserReservation->is_transfer, 
                                                $newUserReservation->reservation_paxes->sum('quantity'), 
                                                $dateFormated,
                                                $newUserReservation->turn->format('H:i\h\s'), 
                                                $newUserReservation->hotel_name, 
                                                public_path('logo-minitrekking.png'))
                                            ));
            } catch (\Throwable $th) {
                Log::debug(print_r([$th->getMessage(), $th->getLine()],  true));
            }
        //
        return response(compact("message", "newUserReservation"));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\UserReservation  $userReservation
     * @return \Illuminate\Http\Response
     */
    public function show(UserReservation $userReservation)
    {
        return $userReservation;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\UserReservation  $userReservation
     * @return \Illuminate\Http\Response
     */
    public function edit(UserReservation $userReservation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateUserReservationRequest  $request
     * @param  \App\Models\UserReservation  $userReservation
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUserReservationRequest $request, UserReservation $id)
    {
        $userReservation = $id;

        $datos = $request->only(['reservation_status_id', 'payment_id', 'payment_details']);

        DB::beginTransaction();
        try {
            switch ($datos['reservation_status_id']) {
                case ReservationStatus::COMPLETED:
                    $userReservation->is_paid = 1;
                    $userReservation->reservation_status_id =  ReservationStatus::COMPLETED;
                    break;
                case ReservationStatus::REJECTED:
                    $userReservation->is_paid = 0;
                    $userReservation->reservation_status_id =  ReservationStatus::REJECTED;

                    RejectedReservation::create([
                        'user_reservation_id'   => $userReservation->id,
                        'data'                  => $datos['payment_details']
                    ]);
                    break;
                default:
                    return response(["message" => "El update solo recibe estatus de REJECTED o COMPLETED Error: URU0001", "error" => "EL reservation_status_id no es valido"], 422);
                    break;
            }
       
            $userReservation->save();
        DB::commit();
        } catch (Exception $error) {
            DB::rollBack();
            return response(["message" => "Tuvimos un problema en el servidor Error: URU0002", "error" => $error->getMessage()], 500);
        } 

        return response()->json(["La reserva fue actualizada con éxito", $userReservation]);

        // $message = "Error al editar {$this->s}.";
        // $datos = $request->all();

        // DB::beginTransaction();
        // try {
        //     if (isset($datos['user'])) {
        //         $user = User::createUser($datos['user']);
        //     }
        //     $id->update($datos + ['user_id' => $datos['user_id']]);

        //     $data = UserReservation::with(['user'])->findOrFail($id->id);
        //     if ($id->reservation_status_id == ReservationStatus::REJECTED) {
        //         RejectedReservation::create([
        //             'reservation_id' => $id->id,
        //         ]);
        //     }
        //     if ($id->reservation_status_id == ReservationStatus::COMPLETED) {
        //         $data->title = "Factura de compra";
        //         try {
        //             $pdf = PDF::loadView('emails.bill', compact('data'));

        //             Mail::send('emails.bill', compact('data'), function ($message) use ($data, $pdf) {
        //                 $message->to($data->user->email, $data->user->email)
        //                     ->subject($data->title)
        //                     ->attachData($pdf->output(), "Billing.pdf");
        //             });
        //         } catch (\Throwable$th) {
        //         }
        //     }
        // } catch (ModelNotFoundException $error) {
        //     DB::rollBack();
        //     return response(["message" => "No se encontro {$this->pr} {$this->s}.", "error" => $error->getMessage()], 404);
        // } catch (Exception $error) {
        //     DB::rollBack();
        //     return response(["message" => $message, "error" => $error->getMessage() . $error->getLine()], 500);
        // }
        // DB::commit();
        // $data = $this->model::with($this->model::SHOW)->findOrFail($id->id);
        // $message = "Se ha editado {$this->pr} {$this->s} correctamente.";
        // return response(compact("message", "data"));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\UserReservation  $userReservation
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserReservation $userReservation)
    {
        //
    }

    private function createPdf($pdfBase, $reservationNumber, $contactName, $withTranslation, $amountOfPaxs, $reservationDate, $reservationTurn, $hotelName, $pathExcurtionLogo)
    {
        // initiate FPDI
        $pdf = new Fpdi();
        // add a page
        $pdf->AddPage();
        // set the source file
        $pdf->setSourceFile($pdfBase);
        // import page 1
        $tplId = $pdf->importPage(1);
        // use the imported page and place it at point 10,10 with a width of 100 mm
        $pdf->useTemplate($tplId, 0, 0, 210);


        //Textos
        $reservationNumber = iconv('UTF-8', 'cp1250', $reservationNumber);
        $contactName = iconv('UTF-8', 'cp1250', $contactName);
        $withTranslation = $withTranslation ? 'con traslado' : '';
        $amountPaxesWithDeatails = iconv('UTF-8', 'cp1250', $amountOfPaxs . 'x Minitrekking ' . $withTranslation);
        $reservationDate = iconv('UTF-8', 'cp1250', $reservationDate);
        $reservationTurn = iconv('UTF-8', 'cp1250', $reservationTurn);
        
        $hotelName = iconv('UTF-8', 'cp1250', $hotelName);
        
        $pathToSavePdf = public_path("reservation-$reservationNumber.pdf");
        
        $details01 = iconv('UTF-8', 'cp1250', 'Por favor, recordá, que el tiempo de espera del pick up puede ');
        $details02 = iconv('UTF-8', 'cp1250', 'ser de hasta 40 minutos.');
        
        $excurtionName = iconv('UTF-8', 'cp1250', 'Minitreking');

        // now write some text above the imported page
        //Nro de reserva
            $pdf->SetFont('Helvetica');
            $pdf->SetTextColor(54, 134, 195);
            $pdf->SetXY(40, 61.4);
            $pdf->Write(0, $reservationNumber);

        //nombre del contact data
            $pdf->SetFont('Helvetica');
            $pdf->SetXY(55, 74);
            $pdf->Write(0, $contactName);

        //cantidad (pasajeros) y nombre de la excursion
            $pdf->SetFont('Helvetica');
            $pdf->SetXY(19, 82);
            $pdf->Write(0, $amountPaxesWithDeatails);


        //Fecha de la reserva
            $pdf->SetFont('Helvetica');
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetXY(22, 92.5);
            $pdf->Write(0, $reservationDate);
        //Hora de la reserva
            $pdf->SetFont('Helvetica');
            $pdf->SetXY(85, 92.5);
            $pdf->Write(0, $reservationTurn);

        //si hay translado poner lo del hotel
        if ($withTranslation) {
            
            $pdf->SetFont('Helvetica','', 12);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetXY(28, 105);
            $str = iconv('UTF-8', 'cp1250', 'El dia de la excursión, el pick up pasará a buscarte ');
            $pdf->Write(0, $str);
            
            $pdf->SetXY(28, 110);
            $str = iconv('UTF-8', 'cp1250', 'por el hotel: ');
            $pdf->Write(0, $str);
            //si hay translado poner lo del hotel
            $pdf->SetFont('Helvetica','', 12);
            $pdf->SetTextColor(54, 134, 195);
            $pdf->Write(0, $hotelName);
            $pdf->Image(public_path('ubicacion.png'), 20, 102, 5);
        }

        //Img
        $pdf->Image($pathExcurtionLogo, 160, 70, 25);

        //Nombre de la excursion
        $pdf->SetFont('Helvetica','', 18);
        $pdf->SetXY(157, 105);
        $pdf->Write(0, $excurtionName);

        //Texto informativo 1
        $pdf->SetFont('Helvetica','', 12);
        $pdf->SetTextColor(42, 42, 42);
        $pdf->SetXY(12, 145);
        $pdf->Write(0, $details01);
        //Texto informativo 1.1
        $pdf->SetXY(12, 150);
        $pdf->Write(0, $details02);

        // $pdf->Output();  
        $pdf->Output($pathToSavePdf, "F");  

        return $pathToSavePdf;

    }
}
