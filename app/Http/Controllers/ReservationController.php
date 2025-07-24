<?php

namespace App\Http\Controllers;

use App\Mail\RegistrationPassword;
use App\Models\User;
use App\Models\UserReservation;
use App\Models\UserReservationObservationsHistory;
use App\Models\UserType;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
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
            $query = UserReservation::with($this->model::INDEX)
                ->when($request->date_from !== null, function ($query) use ($request) {
                    return $query->where('date', '>=', $request->date_from);
                })
                ->when($request->date_to !== null, function ($query) use ($request) {
                    return $query->where('date', '<=', $request->date_to);
                })
                ->when($request->creation_date_from !== null, function ($query) use ($request) {
                    return $query->where('created_at', '>=', $request->creation_date_from);
                })
                ->when($request->creation_date_to !== null, function ($query) use ($request) {
                    return $query->where('created_at', '<=', $request->creation_date_to);
                })
                ->when($request->excurtion_id !== null, function ($query) use ($request) {
                    return $query->where('excurtion_id', $request->excurtion_id);
                })
                ->when($request->reservation_status_id !== null, function ($query) use ($request) {
                    return $query->where('reservation_status_id', $request->reservation_status_id);
                })
                // ->when($request->q !== null, function ($query) use ($request) {
                //     return $query->where('reservation_number', 'LIKE', '%'.$request->q.'%');
                // })
                ->when($request->q !== null, function ($query) use ($request) {
                    return  $query->where(function ($query) use ($request) {
                        $query->where('reservation_number', 'LIKE', '%' . $request->q . '%')
                            ->orWhereHas('user', function ($q) use ($request) {
                                $q->where('email', 'LIKE', '%' . $request->q . '%');
                            });
                    });
                })
                ->when($request->internal_closed !== null, function ($query) use ($request) {
                    return $query->where('internal_closed', $request->internal_closed);
                })
                ->when($request->t !== null, function ($query) use ($request) {
                    return $query->whereHas('user', function ($q) use ($request) {
                        $q->where('email', 'LIKE', '%' . $request->t . '%');
                    });
                })
                ->when($request->agency_id !== null, function ($query) use ($request) {
                    return $query->where('agency_id', $request->agency_id);
                })
                ->when($request->only_web !== null && $request->only_web === 1, function ($query) {
                    return $query->whereNull('agency_id');
                })
                ->orderBy('id', 'desc');

            $total = $query->count();
            $total_per_page = 30;
            $data  = $query->paginate($total_per_page);
            $current_page = $request->page ?? $data->currentPage();
            $last_page = $data->lastPage();

            $data = $data->map(function ($item) {
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

        //devolver cantidad resultados por pagina, cantidad paginas, cantidad resultados total
        return response(compact("message", "data", "total", "total_per_page", "current_page", "last_page"));
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
        $userReservation = $this->getAllReservation($id);

        if (!$userReservation)
            return response(["message" => "No se ha encontrado una reserva para este ID"], 422);

        return response(compact("userReservation"));
    }

    public function getAllReservation($id)
    {
        $reservation = $this->model::with($this->model::SHOW)->find($id);
        $reservation->encrypted_id = Crypt::encryptString($reservation->id);
        $reservation->encrypted_reservation_number = Crypt::encryptString($reservation->reservation_number);
        return $reservation;
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

        if (!$userReservation)
            return response(["message" => "No se ha encontrado una reserva para este ID"], 422);

        $user = User::find($userReservation->user_id);

        if (!$user)
            return response(["message" => "No se ha encontrado el usuario"], 422);

        $pass = Str::random(8);
        $passHashed = Hash::make($pass);
        $user->update(['password' => $passHashed]);

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

        if (!$userReservation)
            return response(["message" => "No se ha encontrado una reserva para este ID"], 422);

        $response = UserReservation::send_mail_user_reservation_voucher($userReservation);

        if ($response['status'] != 200)
            return response(["message" => $response['message'], "status" => $response['status']]);

        return response(["message" => "Mail enviado con exito."]);
    }

    public function update_internal_closed(Request $request, $id)
    {
        $userReservation = UserReservation::find($id);

        if (!$userReservation)
            return response(["message" => "No se ha encontrado una reserva para este ID"], 422);

        $userReservation->internal_closed = $request->internal_closed;
        $userReservation->save();

        $userReservation = $this->getAllReservation($id);

        return response(compact("userReservation"));
    }

    public function new_observation(Request $request)
    {
        $request->validate([
            'user_reservation_id' => 'required',
            'observation' => 'required',
        ]);

        $userReservation = UserReservation::find($request->user_reservation_id);
        if (!$userReservation)
            return response(["message" => "No se ha encontrado una reserva para este ID"], 422);

        $observation = new UserReservationObservationsHistory();
        $observation->user_reservation_id = $request->user_reservation_id;
        $observation->user_id = Auth::user()->id;
        $observation->observation = $request->observation;
        $observation->save();

        $observation = UserReservationObservationsHistory::with(UserReservationObservationsHistory::RELATIONS)->find($observation->id);
        return response(compact("observation"));
    }

    public function change_assigned_user(Request $request)
    {
        $request->validate([
            "reservation_id" => ['required', 'integer', Rule::exists('user_reservations', 'id')],
            "new_user_id" => ['required', 'integer', Rule::exists('users', 'id')],
        ]);

        if (Auth::user()->type_user == UserType::ADMIN)
            return response(["message" => "El usuario no tiene permisos de ADMIN para realizar esta modificacion."], 422);

        $userReservation = UserReservation::find($request->reservation_id);
        $userReservation->user_id = $request->new_user_id;
        $userReservation->save();

        return response(["message" => "Usuario asignado a la reserva con exito.", "userReservation" => $userReservation]);
    }
}
