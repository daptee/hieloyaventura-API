<?php

namespace App\Http\Controllers;

use App\Helpers\UploadFileHelper;
use App\Http\Requests\StoreCharacteristicTypeRequest;
use App\Http\Requests\UpdateCharacteristicTypeRequest;
use App\Models\CharacteristicType;
use App\Models\Icon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class CharacteristicTypeController extends Controller
{
    public $model = CharacteristicType::class;
    public $s = "tipo de caracteristicas"; //sustantivo singular
    public $sp = "tipo de caracteristicas"; //sustantivo plural
    public $ss = "tipo de caracteristica/s"; //sustantivo sigular/plural
    public $v = "o"; //verbo ej:encontrado/a
    public $pr = "el"; //preposicion singular
    public $prp = "los"; //preposicion plural
    public $message_show_500 = "Item no encontrado";
    public $message_show_200 = "Item encontrado";
    public $message_store_500 = "Item no creado";
    public $message_store_200 = "Creado.";

    public function index(Request $request)
    {
        try {
            $data = $this->model::with($this->model::INDEX);
            foreach ($request->all() as $key => $value) {
                if (method_exists($this->model, 'scope' . $key)) {
                    $data->$key($value);
                }
            }
            $data = $this->model::with($this->model::INDEX)->get();
        } catch (ModelNotFoundException $error) {
            return response(["message" => $this->message_404], 404);
        } catch (Exception $error) {
            return response(["message" => $this->message_show_500, "error" => $error->getMessage()], 500);
        }
        $message = $this->message_show_500;
        return response(compact("message", "data"));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(StoreCharacteristicTypeRequest $request)
    {
        $message = "Error al crear en la {$this->s}.";
        $data = $request->all();

        $new = new $this->model($data);
        try {
            $new->save();
            $data = $this->model::with($this->model::SHOW)->findOrFail($new->id);
        } catch (ModelNotFoundException $error) {
            return response(["message" => $this->message_404, "error" => $error->getMessage()], 404);
        } catch (Exception $error) {
            return response(["message" => $this->message_store_500, "error" => $error->getMessage()], 500);
        }
        $message = $this->message_show_200;
        return response(compact("message", "data"));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreCharacteristicTypeRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCharacteristicTypeRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CharacteristicType  $characteristicType
     * @return \Illuminate\Http\Response
     */
    public function show(CharacteristicType $characteristicType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CharacteristicType  $characteristicType
     * @return \Illuminate\Http\Response
     */
    public function edit(CharacteristicType $characteristicType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCharacteristicTypeRequest  $request
     * @param  \App\Models\CharacteristicType  $characteristicType
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCharacteristicTypeRequest $request, CharacteristicType $characteristicType)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CharacteristicType  $characteristicType
     * @return \Illuminate\Http\Response
     */
    public function destroy(CharacteristicType $characteristicType)
    {
        //
    }
}
