<?php

namespace App\Http\Controllers;

use App\Mail\RegistrationPassword;
use App\Models\User;
use App\Models\UserReservation;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;

class ReservationController extends Controller
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
            $data = UserReservation::with($this->model::INDEX)->when($request->date_from, function ($query) use ($request) {
                return $query->where('date', '>=', $request->date_from);
            })
            ->when($request->date_to, function ($query) use ($request) {
                return $query->where('date', '<=', $request->date_to);
            })
            ->when($request->excurtion_id, function ($query) use ($request) {
                return $query->where('excurtion_id', $request->excurtion_id);
            })
            ->when($request->reservation_status_id, function ($query) use ($request) {
                return $query->where('reservation_status_id', $request->reservation_status_id);
            })
            ->orderBy('id', 'desc')
            ->paginate(30)
            ->map(function ($item) {
                $item->encrypted_id = Crypt::encryptString($item->id);
                $item->encrypted_reservation_number = Crypt::encryptString($item->reservation_number);
                return $item;
            });

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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $userReservation = UserReservation::with($this->model::SHOW)->find($id);

        if(!$userReservation)
            return response(["message" => "No se ha encontrado una reserva para este ID"], 422);

        $userReservation->encrypted_reservation_number = Crypt::encryptString($userReservation->reservation_number);

        return response(compact("userReservation"));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function resend_email_welcome(Request $request)
    {
        $userReservation = UserReservation::find($request->user_reservation_id);
        
        if(!$userReservation)
            return response(["message" => "No se ha encontrado una reserva para este ID"], 422);
        
        $user = User::find($userReservation->user_id);

        if(!$user)
            return response(["message" => "No se ha encontrado el usuario"], 422);

        $pass = Str::random(8);
        $passHashed = Hash::make($pass);
        $user->update([ 'password' => $passHashed ]);
        
        //Email de Bienvenida
            try {
                Mail::to($user->email)->send(new RegistrationPassword($user->email, $pass, $userReservation->language_id));
            } catch (\Throwable $th) {
                Log::debug(print_r([$th->getMessage(), $th->getLine()],  true));
                return response(["message" => "Error al enviar el mail."], 500);
            }
        //

        return response(["message" => "Mail enviado con exito."]);
    }

    public function resend_email_voucher(Request $request)
    {
        $userReservation = UserReservation::find($request->user_reservation_id);
        
        if(!$userReservation)
            return response(["message" => "No se ha encontrado una reserva para este ID"], 422);
       
        $response = UserReservation::send_mail_user_reservation_voucher($userReservation);
    
        if($response['status'] != 200)
            return response(["message" => $response['message'], "status" => $response['status']]);

        return response(["message" => "Mail enviado con exito."]);
    }
}
