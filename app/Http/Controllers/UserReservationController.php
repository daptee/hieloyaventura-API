<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserReservationRequest;
use App\Http\Requests\UpdateUserReservationRequest;
use App\Mail\RegistrationPassword;
use App\Models\Pax;
use App\Models\RejectedReservation;
use App\Models\ReservationPax;
use App\Models\ReservationStatus;
use App\Models\User;
use App\Models\UserReservation;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PDF;

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
            $data = $this->model::with($this->model::INDEX)->get();
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

        DB::beginTransaction();
        try {
            if (isset($datos['user'])) {
                $pass = Hash::make(Str::random(8));
                
                $user = User::where('email', $datos['user']['email'])->first();
                if (!$user) {
                    $user = User::createUser($datos['user'] + [
                        'password' => $pass,
                    ]);
                }


                try {
                    Mail::to($datos['user']['email'])->send(new RegistrationPassword($pass));
                } catch (\Throwable $th) {
                }
            }
            $data = new $this->model($datos + [
                "reservation_status_id" => ReservationStatus::STARTED,
                "user_id" => $datos['user_id'] ?? $user->id,
            ]);
            $data->save();
            if (isset($datos['paxs'])) {
                foreach ($datos['paxs'] as $pax) {
                    Pax::create($pax + ['user_reservation_id' => $data->id]);
                }
            }
            if (isset($datos['paxs_reservation'])) {
                foreach ($datos['paxs_reservation'] as $paxs) {
                    ReservationPax::create($paxs + ['user_reservation_id' => $data->id]);
                }
            }
            $data = $this->model::with($this->model::SHOW)->findOrFail($data->id);
        } catch (ModelNotFoundException $error) {
            DB::rollBack();
            return response(["message" => "No se encontraron {$this->prp} {$this->sp}.", "error" => $error->getMessage()], 404);
        } catch (Exception $error) {
            DB::rollBack();
            \Log::debug( print_r([$error->getMessage(), $error->getLine()], true));
            return response(compact("message"), 500);
        }
        DB::commit();

        $message = "Se ha creado {$this->pr} {$this->s} correctamente.";
        return response(compact("message", "data"));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\UserReservation  $userReservation
     * @return \Illuminate\Http\Response
     */
    public function show(UserReservation $userReservation)
    {
        //
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
        $message = "Error al editar {$this->s}.";
        $datos = $request->all();

        DB::beginTransaction();
        try {
            if (isset($datos['user'])) {
                $user = User::createUser($datos['user']);
            }
            $id->update($datos + ['user_id' => $datos['user_id']]);

            $data = UserReservation::with(['user'])->findOrFail($id->id);
            if ($id->reservation_status_id == ReservationStatus::REJECTED) {
                RejectedReservation::create([
                    'reservation_id' => $id->id,
                ]);
            }
            if ($id->reservation_status_id == ReservationStatus::COMPLETED) {
                $data->title = "Factura de compra";
                try {
                    $pdf = PDF::loadView('emails.bill', compact('data'));

                    Mail::send('emails.bill', compact('data'), function ($message) use ($data, $pdf) {
                        $message->to($data->user->email, $data->user->email)
                            ->subject($data->title)
                            ->attachData($pdf->output(), "Billing.pdf");
                    });
                } catch (\Throwable$th) {
                }
            }
        } catch (ModelNotFoundException $error) {
            DB::rollBack();
            return response(["message" => "No se encontro {$this->pr} {$this->s}.", "error" => $error->getMessage()], 404);
        } catch (Exception $error) {
            DB::rollBack();
            return response(["message" => $message, "error" => $error->getMessage() . $error->getLine()], 500);
        }
        DB::commit();
        $data = $this->model::with($this->model::SHOW)->findOrFail($id->id);
        $message = "Se ha editado {$this->pr} {$this->s} correctamente.";
        return response(compact("message", "data"));
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
}
