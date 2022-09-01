<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReservationStatusRequest;
use App\Http\Requests\UpdateReservationStatusRequest;
use App\Models\ReservationStatus;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Request;

class ReservationStatusController extends Controller
{
    public $model = ReservationStatus::class;
    public $s = "estado de reserva"; //sustantivo singular
    public $sp = "estado de reservas"; //sustantivo plural
    public $ss = "estado de reserva/s"; //sustantivo sigular/plural
    public $v = "o"; //verbo ej:encontrado/a
    public $pr = "el"; //preposicion singular
    public $prp = "los"; //preposicion plural

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
     * @param  \App\Http\Requests\StoreReservationStatusRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreReservationStatusRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ReservationStatus  $reservationStatus
     * @return \Illuminate\Http\Response
     */
    public function show(ReservationStatus $reservationStatus)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ReservationStatus  $reservationStatus
     * @return \Illuminate\Http\Response
     */
    public function edit(ReservationStatus $reservationStatus)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateReservationStatusRequest  $request
     * @param  \App\Models\ReservationStatus  $reservationStatus
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateReservationStatusRequest $request, ReservationStatus $reservationStatus)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ReservationStatus  $reservationStatus
     * @return \Illuminate\Http\Response
     */
    public function destroy(ReservationStatus $reservationStatus)
    {
        //
    }
}
