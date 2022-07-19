<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCharacteristicRequest;
use App\Http\Requests\UpdateCharacteristicRequest;
use App\Models\Characteristic;
use App\Models\ExcurtionCharacteristic;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class CharacteristicController extends Controller
{
    public $model = Characteristic::class;
    public $s = "caracteristica"; //sustantivo singular
    public $sp = "caracteristicas"; //sustantivo plural
    public $ss = "caracteristica/s"; //sustantivo sigular/plural
    public $v = "a"; //verbo ej:encontrado/a
    public $pr = "la"; //preposicion singular
    public $prp = "las"; //preposicion plural
    public $message_show_500 = "Item no encontrado";
    public $message_show_200 = "Item encontrado";
    public $message_store_500 = "Item no creado";
    public $message_store_200 = "Creado.";

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
     * @param  \App\Http\Requests\StoreCharacteristicRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCharacteristicRequest $request)
    {
        $message = "Error al crear en la {$this->s}.";
        $data = $request->all();

        $data = new $this->model($data);
        try {
            $data->save();
            if (isset($data['excurtion_id'])) {
                ExcurtionCharacteristic::create(['excurtion_id' => $data['excurtion_id'], 'characteristic_id' => $data->id]);
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
     * @param  \App\Models\Characteristic  $characteristic
     * @return \Illuminate\Http\Response
     */
    public function show(Characteristic $characteristic)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Characteristic  $characteristic
     * @return \Illuminate\Http\Response
     */
    public function edit(Characteristic $characteristic)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateExcurtionRequest  $request
     * @param  \App\Models\Excurtion  $excurtion
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCharacteristicRequest $request, $id)
    {
        $message = "Error al editar {$this->s}.";
        $datos = $request->all();

        DB::beginTransaction();
        try {
            $this->model::findOrFail($id);
            Characteristic::updateCharacteristic2($datos + ['id' => $id]);
        } catch (ModelNotFoundException $error) {
            DB::rollBack();
            return response(["message" => "No se encontro {$this->pr} {$this->s}.", "error" => $error->getMessage()], 404);
        } catch (Exception $error) {
            DB::rollBack();
            return response(["message" => $message, "error" => $error->getMessage()], 500);
        }
        DB::commit();
        $data = $this->model::with($this->model::SHOW)->findOrFail($id);
        $message = "Se ha editado {$this->pr} {$this->s} correctamente.";
        return response(compact("message", "data"));
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Characteristic  $characteristic
     * @return \Illuminate\Http\Response
     */
    public function destroy(Characteristic $characteristic)
    {
        //
    }
}
