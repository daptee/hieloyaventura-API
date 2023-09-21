<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserReservationAgencyRequest;
use App\Http\Requests\StoreUserReservationRequest;
use App\Http\Requests\UpdateUserReservationRequest;
use App\Http\Requests\UpdateUserReservationStatusRequest;
use App\Mail\RegistrationPassword;
use App\Models\BillingDataReservation;
use App\Models\ContactDataReservation;
use App\Models\Pax;
use App\Models\RejectedReservation;
use App\Models\ReservationPax;
use App\Models\ReservationStatus;
use App\Models\User;
use App\Models\UserReservation;
use App\Models\UserReservationStatusHistory;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
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
            foreach($data as $item){
//                $item->encrypted_id = Crypt::encryptString($item->id);
                $item->encrypted_reservation_number = Crypt::encryptString($item->reservation_number);
            }
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
        $reservation_number = $datos['reservation_number'];
        try {
            DB::beginTransaction();
                // Crear un usuario si se manda "create_user" en true
                $user = User::where('email', $datos['contact_data']['email'])->first(); // Busco el usuario en DB
                    if ($datos['create_user'] and isset($datos['contact_data']) and !$user) {
                        $pass = Str::random(8);
                        $passHashed = Hash::make($pass);
                        $user = User::createUser($datos['contact_data'] + [
                            'password' => $passHashed,
                        ]);
                        //Email de Bienvenida
                            try {
                                Mail::to($datos['contact_data']['email'])->send(new RegistrationPassword($datos['contact_data']['email'], $pass, $datos['language_id']));
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

                    // Guardo status en historial
                    UserReservation::store_user_reservation_status_history(ReservationStatus::STARTED, $newUserReservation->id);
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
                        BillingDataReservation::create($datos['billing_data'] + ['user_reservation_id' => $newUserReservation->id ]);
                    }
                //contact data reservation
                    if(isset($datos['contact_data'])) {
                        ContactDataReservation::create($datos['contact_data'] + ['user_reservation_id' => $newUserReservation->id]);
                    }
                //

            DB::commit();
        } catch (ModelNotFoundException $error) {
            DB::rollBack();
            Log::debug( print_r(["Error al crear la reserva (1er catch, detalle: " . $error->getMessage() . " datos a cargar: $datos", $error->getLine()], true));
            return response(["message" => "No se encontraron {$this->prp} {$this->sp}.", "error" => $error->getMessage()], 404);
        } catch (Exception $error) {
            DB::rollBack();
            Log::debug( print_r(["Error al crear la reserva, detalle: " . $error->getMessage() . " datos a cargar: $datos, nro reserva: $reservation_number", $error->getLine()], true));
            return response(["message" => $message, "error" => "URC0001"], 500);
        }

        $message = "Se ha creado {$this->pr} {$this->s} correctamente.";
        $newUserReservation = $this->model::with($this->model::SHOW)->findOrFail($newUserReservation->id);

        return response(compact("message", "newUserReservation"));
    }

    public function store_type_agency(StoreUserReservationAgencyRequest $request)
    {
        $message = "Error al crear en la {$this->s}.";
        $datos = $request->all();
    
        // validar token -> agency
        if(Auth::guard('agency')->user()->agency_code != $request->agency_code)
            return response(["message" => "agency_id invalido"], 400);

        try {
            DB::beginTransaction();
                //Creo el registro en user_reservations
                    $newUserReservation = new $this->model($datos + ["reservation_status_id" => ReservationStatus::STARTED]);
                    $newUserReservation->user_id = null;
                    $newUserReservation->agency_id = $request->agency_code;
                    $newUserReservation->language_id = 1;
                    $newUserReservation->save();

                    // Guardo status en historial
                    UserReservation::store_user_reservation_status_history(ReservationStatus::STARTED, $newUserReservation->id);
                //

            DB::commit();
        } catch (ModelNotFoundException $error) {
            DB::rollBack();
            $nro_reserva = $datos['reservation_number'];
            Log::debug( print_r(["Error al crear la reserva (agencia) (1er catch, detalle: " . $error->getMessage() . " nro_reserva: $nro_reserva", $error->getLine()], true));
            return response(["message" => "No se encontraron {$this->prp} {$this->sp}.", "error" => $error->getMessage()], 404);
        } catch (Exception $error) {
            DB::rollBack();
            $nro_reserva = $datos['reservation_number'];
            Log::debug( print_r(["Error al crear la reserva (agencia), detalle: " . $error->getMessage() . " nro_reserva: $nro_reserva", $error->getLine()], true));
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

        $userReservation->encrypted_reservation_number = Crypt::encryptString($userReservation->reservation_number);
        return $userReservation;
    }

    public function getByReservationNumber($reservation_number)
    {
        try {
            $userReservation = UserReservation::with(['user','status', 'excurtion', 'billing_data', 'contact_data', 'paxes', 'reservation_paxes'])->where('reservation_number', $reservation_number)->first();

            if(is_null($userReservation))
                return response(["message" => "No se ha encontrado una reserva para este numero de reserva"], 422);

            $userReservation->encrypted_reservation_number = Crypt::encryptString($userReservation->reservation_number);
            return $userReservation;
        } catch (Exception $error) {
            Log::debug( print_r(["Error al obtener reservation by number, detalle: " . $error->getMessage(), $error->getLine()], true));
        }
    }

    public function getByReservationNumberEncrypted($reservation_number_encrypted)
    {
        try {
            $reservation_number_decrypted = Crypt::decryptString($reservation_number_encrypted);
            $userReservation = UserReservation::with(['user','status', 'excurtion', 'billing_data', 'contact_data', 'paxes', 'reservation_paxes'])->where('reservation_number', $reservation_number_decrypted)->first();

            if(is_null($userReservation))
                return response(["message" => "No se ha encontrado una reserva para este numero de reserva"], 422);

            $userReservation->encrypted_reservation_number = Crypt::encryptString($userReservation->reservation_number);
            return $userReservation;
        } catch (Exception $error) {
            Log::debug( print_r(["Error al obtener reservation by number encrypted, detalle: " . $error->getMessage(), $error->getLine()], true));
        }
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

        $datos = $request->only(['reservation_status_id', 'payment_details']);

        DB::beginTransaction();
        try {
            switch ($datos['reservation_status_id']) {
                case ReservationStatus::PAX_PENDING:
                    $userReservation->is_paid = 1;
                    $status_id = ReservationStatus::PAX_PENDING;
                    $userReservation->reservation_status_id = $status_id;
                    Log::debug([
                        "Response confirma pasajeros" => $request->response_cp,
                        "Comportamiento funcion" => $request->funcion_part 
                    ]);
                    
                    break;
                case ReservationStatus::REJECTED:
                    $userReservation->is_paid = 0;
                    $status_id = ReservationStatus::REJECTED;
                    $userReservation->reservation_status_id = $status_id;

                    RejectedReservation::create([
                        'user_reservation_id'   => $userReservation->id,
                        'data'                  => $datos['payment_details']
                    ]);
                    break;
                case ReservationStatus::AUTOMATIC_CANCELED:
                        $userReservation->is_paid = 0;
                        $status_id = ReservationStatus::AUTOMATIC_CANCELED;
                        $userReservation->reservation_status_id = $status_id;
                        
                        break;
                default:
                    return response([
                        "message" => "El update solo recibe estatus de REJECTED o PAX_PENDING o AUTOMATIC_CANCELED Error: URU0001",
                        "error" => "EL reservation_status_id no es valido"
                    ], 422);
                    break;
            }

            $userReservation->save();

            if($status_id)
                UserReservation::store_user_reservation_status_history($status_id, $userReservation->id);

            $userReservation->encrypted_reservation_number = Crypt::encryptString($userReservation->reservation_number);
        DB::commit();
        } catch (Exception $error) {
            DB::rollBack();
            Log::debug( print_r(["Error al hacer update de la reserva, detalle: " . $error->getMessage(), $error->getLine()], true));
            return response(["message" => "Tuvimos un problema en el servidor Error: URU0002", "error" => $error->getMessage()], 500);
        }

        return response()->json(["La reserva fue actualizada con éxito", $userReservation]);
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

    public function test_cancelar_reserva()
    {
        $reservations = UserReservation::whereIn('reservation_status_id', [ReservationStatus::REJECTED, ReservationStatus::STARTED])
                                    ->where('created_at', '<', now()->modify('-30 minute')->format('Y-m-d H:i:s'))
                                    ->where('reservation_number', '!=', 0)
                                    ->get();
    
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

                        UserReservation::store_user_reservation_status_history(ReservationStatus::AUTOMATIC_CANCELED, $reservation->id);
                    }
                }

            }
        }

        return response()->json(["message" => "Test cancelar reservas completo"]);

    }
}
