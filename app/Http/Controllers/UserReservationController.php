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
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
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
                    $newUserReservation->language_id = $datos['language_id'] ?? 1; // Agregar en tabla de DB y avisar a Diego
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
        
        return response(compact("message", "newUserReservation"));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\UserReservation  $userReservation
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $userReservation = UserReservation::with(['user','status', 'excurtion', 'billing_data', 'contact_data', 'paxes', 'reservation_paxes'])->find($id);
        
        if(is_null($userReservation))
            return response(["message" => "No se ha encontrado una reserva para este ID"], 422);

        return $userReservation;
    }

    public function getByReservationNumber($reservation_number)
    {
        $userReservation = UserReservation::with(['user','status', 'excurtion', 'billing_data', 'contact_data', 'paxes', 'reservation_paxes'])->where('reservation_number', $reservation_number)->first();
      
        if(is_null($userReservation))
            return response(["message" => "No se ha encontrado una reserva para este numero de reserva"], 422);

        return $userReservation;
    }

    public function getByReservationNumberEncrypted($reservation_number_encrypted)
    {
        $reservation_number_decrypted = Crypt::decryptString($reservation_number_encrypted);
        $userReservation = UserReservation::with(['user','status', 'excurtion', 'billing_data', 'contact_data', 'paxes', 'reservation_paxes'])->where('reservation_number', $reservation_number_decrypted)->first();
      
        if(is_null($userReservation))
            return response(["message" => "No se ha encontrado una reserva para este numero de reserva"], 422);

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

        $datos = $request->only(['reservation_status_id', 'payment_id', 'payment_details', 'email']);

        DB::beginTransaction();
        try {
            switch ($datos['reservation_status_id']) {
                case ReservationStatus::COMPLETED:
                    $userReservation->is_paid = 1;
                    $userReservation->reservation_status_id =  ReservationStatus::COMPLETED;

                    //Mandar email con el PDF adjunto
                        try {
                            DB::beginTransaction();
                                $pathReservationPdf = $this->createPdf(
                                    $userReservation,
                                    'Por favor, recordá, que el tiempo de espera del pick up puede ser de hasta 40 minutos.'
                                );                                
                                $userReservation->pdf = $pathReservationPdf['urlToSave'];
                                $userReservation->save();

                                DB::commit();
                        } catch (\Throwable $th) {
                            DB::rollBack();
                            Log::debug(print_r([$th->getMessage(), $th->getLine()],  true));
                        }
                        
                        try{
                            $mailTo = $userReservation->contact_data->email;
                            $is_bigice = $userReservation->excurtion_id == 2 ? true : false;
                            $hash_reservation_number = Crypt::encryptString($userReservation->reservation_number);
                            $reservation_number = $userReservation->reservation_number;
                            $excurtion_name = $userReservation->excurtion->name;

                            Mail::to($mailTo)->send(new MailUserReservation($mailTo, $pathReservationPdf['pathToSavePdf'], $is_bigice, $hash_reservation_number, $reservation_number, $excurtion_name));
                        } catch (\Throwable $th) {
                            Log::debug(print_r([$th->getMessage(), $th->getLine()],  true));
                        }
                    //
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

    // public function testpdf($trf, $excursion)
    // {
    //     $userReservation = UserReservation::latest()->first();
    //     $userReservation->is_transfer = $trf;
    //     $userReservation->excurtion_id = $excursion;

    //     $pathReservationPdf = $this->createPdf($userReservation,'Por favor, recordá, que el tiempo de espera del pick up puede ser de hasta 40 minutos.');
        
    //     $userReservation->pdf = $pathReservationPdf['urlToSave'];
    //     $userReservation->save();

    //     return response()->json($userReservation);
    // }
    
    private function createPdf($newUserReservation, $details)
    {
        // if(is_null($lenguageToPdf)){
        //     Carbon::setLocale('es');
        //     $languageToPdf = "ES";
        // }

        // Language
        $array_languages = [ 
            1 => 'ES',
            2 => 'EN',
            3 => 'PT'
        ];

        $language_id = $newUserReservation->language_id ?? 1;
        $languageToPdf = $array_languages[$language_id];

        if(!is_dir('reservations'))
            mkdir(public_path("reservations"));

        $date = $newUserReservation->date;
        $dayText = ucfirst($date->translatedFormat('l'));
        $dayNumber = $date->format('j');
        $month = ucfirst($date->translatedFormat('F'));
        $dateFormated = "$dayText $dayNumber de $month";

        $excurtionName = $newUserReservation->excurtion->name;
        $pathExcurtionLogo = public_path($newUserReservation->excurtion->icon);
        

        $firstPage = $this->withOrWithoutTrf($excurtionName, $newUserReservation->is_transfer, $languageToPdf);
        $secondPage = public_path("excursions/bases/$languageToPdf.pdf");

        // initiate FPDI
        $pdf = new Fpdi();

        $pdf->AddPage();
        // set the source file
        $pdf->setSourceFile($firstPage);
        $tplId1 = $pdf->importPage(1);

        $pdf->useTemplate($tplId1, -8, -8, 227);

        // add a page
        $pdf->AddPage();
        // set the source file
        $pdf->setSourceFile($secondPage);
        // import page 1
        $tplId = $pdf->importPage(1);
        // use the imported page
        $pdf->useTemplate($tplId, 0, 0, 210);


        $traduccionesPDF = [
            'ES' => [
                'de_dateFormated' => 'de',
                'thanks' => '¡Gracias por tu compra',
                'withTranslation' => 'con traslado',
                'withTranslationHotel' => 'El dia de la excursión, el pick up pasará a buscarte',
                'por_el_hotel' => 'por el hotel'
            ],
            'EN' => [
                'de_dateFormated' => 'of',
                'thanks' => 'Thanks for your purchase',
                'withTranslation' => 'with transfer',
                'withTranslationHotel' => 'On the day of the excursion, the pick up will pick you up',
                'por_el_hotel' => 'by the hotel'
            ],
            'PT' => [
                'de_dateFormated' => 'do',
                'thanks' => 'Obrigado pela sua compra',
                'withTranslation' => 'com transferência',
                'withTranslationHotel' => 'No dia da excursão, o pick up irá buscá-lo',
                'por_el_hotel' => 'pelo hotel'
            ]
        ];
        
        //Textos
        $thanks                  = iconv('UTF-8', 'ISO-8859-1', $traduccionesPDF[$languageToPdf]['thanks']);
        $reservationNumber       = iconv('UTF-8', 'cp1250', "#$newUserReservation->reservation_number");
        $contactFullName         = iconv('UTF-8', 'cp1250', $newUserReservation->contact_data->name . " " . $newUserReservation->contact_data->lastname);
        $contactName             = iconv('UTF-8', 'cp1250', $newUserReservation->contact_data->name);
        $withTranslation         = $newUserReservation->is_transfer ? ' ' . $traduccionesPDF[$languageToPdf]['withTranslation'] : '';
        $amountPaxesWithDeatails = iconv('UTF-8', 'cp1250', $newUserReservation->reservation_paxes->sum('quantity') . "x $excurtionName");
        $reservationDate         = iconv('UTF-8', 'cp1250', $dateFormated);
        $reservationTurn         = iconv('UTF-8', 'cp1250', $newUserReservation->turn->format('H:i\h\s'));
        $hotelName               = iconv('UTF-8', 'cp1250', $newUserReservation->hotel_name);
        $details                 = iconv('UTF-8', 'cp1250', $details);
        $excurtionName           = iconv('UTF-8', 'cp1250', $excurtionName);
        $namePdf                 = "reservation-$newUserReservation->id" . "-$newUserReservation->reservation_number.pdf";
        $pathToSavePdf           = public_path("reservations/$namePdf");
        $urlToSave               = url("reservations/$namePdf");

        // now write some text above the imported page
        //Nro de reserva
        $pdf->AddFont('Nunito-Light','','Nunito-Light.php');
        $pdf->AddFont('Nunito-Regular','','Nunito-Regular.php');
        $pdf->AddFont('Nunito-SemiBold','','Nunito-SemiBold.php');
        $pdf->AddFont('Nunito-Bold','','Nunito-Bold.php');
        $pdf->AddFont('Nunito-Medium','','Nunito-Medium.php');
        $pdf->AddFont('GothamRounded-Bold','','GothamRounded-Bold.php');

        //Agradecimiento por la compra
            $pdf->SetFont('GothamRounded-Bold', '', 14);
            $pdf->SetTextColor(12, 180, 181);
            $pdf->SetXY(10, 35);
            $pdf->Write(0, "$thanks $contactName!");
        
        //Nro de reserva
            $pdf->SetFont('Nunito-Bold', '', 12);
            $pdf->SetTextColor(54, 134, 195);
            $pdf->SetXY(40, 61.4);
            $pdf->Write(0, $reservationNumber);

        //nombre del contact data
            $pdf->SetFont('Nunito-SemiBold', '', 12);
            $pdf->SetTextColor(54, 134, 195);
            $pdf->SetXY(55, 74);
            $pdf->Write(0, $contactFullName);

        //cantidad (pasajeros) y nombre de la excursion
            $pdf->SetFont('Nunito-Regular', '', 12);
            $pdf->SetXY(19, 82);
            $pdf->Write(0, $amountPaxesWithDeatails);

            $pdf->SetFont('Nunito-Bold', '', 12);
            // $pdf->SetXY(19, 82);
            $pdf->Write(0, $withTranslation);


        //Fecha de la reserva
            $pdf->SetFont('Nunito-Bold', '', 12);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetXY(19, 88.2);
            $pdf->MultiCell(62, 8.6, $reservationDate, 0, 'C');
        //Hora de la reserva
            // $pdf->SetXY(84, 92.5);
            $pdf->SetXY(83.5, 88.1);
            $pdf->MultiCell(20.5, 8.8, $reservationTurn, 0, 'C');

        //si hay translado poner lo del hotel
        if ($withTranslation) {
            
            $pdf->Image(public_path('ubicacion.png'),20, 105, 5);
            $pdf->SetFont('Nunito-Light','', 12);
            $pdf->SetTextColor(42, 42, 42);
            $pdf->SetXY(28, 108);
            $str = iconv('UTF-8', 'cp1250', $traduccionesPDF[$languageToPdf]['withTranslationHotel'] . ' ');
            $pdf->Write(0, $str);
            
            $pdf->SetXY(28, 113);
            $str = iconv('UTF-8', 'cp1250', $traduccionesPDF[$languageToPdf]['por_el_hotel'] . ': ');
            $pdf->Write(0, $str);
            //si hay translado poner lo del hotel
            $pdf->SetFont('Nunito-SemiBold','', 12);
            $pdf->SetTextColor(54, 134, 195);
            $pdf->Write(0, $hotelName);
            //Texto informativo 1
            $pdf->SetFont('Nunito-Regular','', 12);
            $pdf->SetTextColor(42, 42, 42);

            $pdf->SetXY(10, 142);
            $pdf->MultiCell(120, 5, $details, 0, 'L');
        }

        //Img
        $pdf->Image($pathExcurtionLogo, 159, 67, 25);

        //Nombre de la excursion
        $pdf->SetFont('GothamRounded-Bold','', 18);
        
        $pdf->SetXY(140, 98);
        $pdf->SetTextColor(54, 134, 195);
        // $pdf->Write(0, $excurtionName);
        $pdf->MultiCell(62, 8, $excurtionName, 0, 'C');


        // $pdf->Output();  
        $pdf->Output($pathToSavePdf, "F");  

        return [
            'urlToSave' => $urlToSave, 
            'pathToSavePdf' => $pathToSavePdf
        ];

    }   

    private function withOrWithoutTrf( $excursionName, $transfer, $language = 'ES')
    {
        $withOrWithoutTrf = $transfer ? 'con-trf' : 'sin-trf' ;

        $excursionName = strtolower($excursionName);

        return public_path("excursions/$excursionName/pdfs/$withOrWithoutTrf/$language.pdf");
    }
}
