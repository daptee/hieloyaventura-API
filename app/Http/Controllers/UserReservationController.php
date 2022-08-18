<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserReservationRequest;
use App\Http\Requests\UpdateUserReservationRequest;
use App\Models\Pax;
use App\Models\ReservationStatus;
use App\Models\User;
use App\Models\UserReservation;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;

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
    public function index()
    {
        //
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
            if (isset($datos['user'])) {
                $user = User::createUser($datos['user']);
            }
            $data = new $this->model($datos + ["reservation_status_id" => ReservationStatus::INICIADA]);
            $data->save();
            if (isset($datos['paxs'])) {
                foreach ($datos['paxs'] as $pax) {
                    $pax['password'] = Hash::make($pax['password']);
                    Pax::create($pax + ['user_reservation_id' => $data->id]);
                }
            }
            if (isset($datos['user'])) {
                $user = User::createUser($datos['user']);
            }

            $data = $this->model::with($this->model::SHOW)->findOrFail($data->id);
        } catch (ModelNotFoundException $error) {
            return response(["message" => "No se encontraron {$this->prp} {$this->sp}.", "error" => $error->getMessage()], 404);
        } catch (Exception $error) {
            return response(compact("message", $error->getMessage()), 500);
        }
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
    public function update(UpdateUserReservationRequest $request, UserReservation $userReservation)
    {
        //
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
