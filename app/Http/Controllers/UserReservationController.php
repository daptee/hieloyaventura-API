<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserReservationRequest;
use App\Http\Requests\UpdateUserReservationRequest;
use App\Mail\RegistrationPassword;
use App\Models\BillingDataReservation;
use App\Models\ContactDataReservation;
use App\Models\Pax;
use App\Models\RejectedReservation;
use App\Models\ReservationPax;
use App\Models\ReservationStatus;
use App\Models\User;
use App\Models\UserReservation;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Carbon\Carbon;
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
                $item->encrypted_id = Crypt::encryptString($item->id);
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

        $userReservation->encrypted_reservation_number = Crypt::encryptString($userReservation->reservation_number);
        return $userReservation;
    }

    public function getByReservationNumber($reservation_number)
    {
        $userReservation = UserReservation::with(['user','status', 'excurtion', 'billing_data', 'contact_data', 'paxes', 'reservation_paxes'])->where('reservation_number', $reservation_number)->first();
      
        if(is_null($userReservation))
            return response(["message" => "No se ha encontrado una reserva para este numero de reserva"], 422);

        $userReservation->encrypted_reservation_number = Crypt::encryptString($userReservation->reservation_number);
        return $userReservation;
    }

    public function getByReservationNumberEncrypted($reservation_number_encrypted)
    {
        $reservation_number_decrypted = Crypt::decryptString($reservation_number_encrypted);
        $userReservation = UserReservation::with(['user','status', 'excurtion', 'billing_data', 'contact_data', 'paxes', 'reservation_paxes'])->where('reservation_number', $reservation_number_decrypted)->first();
      
        if(is_null($userReservation))
            return response(["message" => "No se ha encontrado una reserva para este numero de reserva"], 422);

        $userReservation->encrypted_reservation_number = Crypt::encryptString($userReservation->reservation_number);
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
                case ReservationStatus::PAX_PENDING:
                    $userReservation->is_paid = 1;
                    $userReservation->reservation_status_id =  ReservationStatus::PAX_PENDING;
 
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
                    return response(["message" => "El update solo recibe estatus de REJECTED o PAX_PENDING Error: URU0001", "error" => "EL reservation_status_id no es valido"], 422);
                    break;
            }
       
            $userReservation->save();

            $userReservation->encrypted_reservation_number = Crypt::encryptString($userReservation->reservation_number);
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
    
}
