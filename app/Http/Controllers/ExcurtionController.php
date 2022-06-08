<?php

namespace App\Http\Controllers;

use App\Helpers\UploadFileHelper;
use App\Http\Requests\StoreCharacteristicRequest;
use App\Http\Requests\StoreCharacteristicTranslableRequest;
use App\Http\Requests\StoreCharacteristicTypeRequest;
use App\Http\Requests\StoreExcurtionRequest;
use App\Http\Requests\UpdateExcurtionRequest;
use App\Models\Characteristic;
use App\Models\CharacteristicTranslable;
use App\Models\CharacteristicType;
use App\Models\Excurtion;
use App\Models\ExcurtionCharacteristic;
use App\Models\Icon;
use App\Models\PictureExcurtion;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExcurtionController extends Controller
{
    public $model = Excurtion::class;
    public $s = "excurcion"; //sustantivo singular
    public $sp = "excurciones"; //sustantivo plural
    public $ss = "excurcion/es"; //sustantivo sigular/plural
    public $v = "a"; //verbo ej:encontrado/a
    public $pr = "la"; //preposicion singular
    public $prp = "las"; //preposicion plural
    public $message_show_500 = "Item no encontrado";
    public $message_show_200 = "Item encontrado";
    public $message_store_500 = "Item no creado";
    public $message_store_200 = "Creado.";

    // public function __construct(array $data)
    // {
    //     switch (Config::get('app.locale') ?? 1) {
    //         case 'es':
    //             $this->message_404 = "No se encontraron " . $this->sp . ".";
    //             $this->message_show_500 = "Error al traer listado de {$this->sp}.";
    //             $this->message_show_200 = ucfirst($this->sp) . " encontrad{$this->v}s exitosamente.";
    //             $this->message_store_500 = "Error al crear en la {$this->s}.";
    //             $this->message_store_200 = "Se ha creado {$this->pr} {$this->s} correctamente.";
    //             break;

    //         default:
    //             break;
    //     }
    // }

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
        $message = $this->message_show_200;
        return response(compact("message", "data"));
    }

    public function store(StoreExcurtionRequest $request)
    {
        $message = "Error al crear en la {$this->s}.";
        $data = $request->all();
// dd($data);
        $new_excurtion = new $this->model($data);
        DB::beginTransaction();
        try {
            $new_excurtion->save();
            if (isset($data['pictures'])) {
                foreach ($data['pictures'] as $picture) {
                    $link = UploadFileHelper::createFiles($picture['icon']['file'], 'pictureExcurtion', 'image', '');
                    PictureExcurtion::create(['link' => $link, 'excurtion_id' => $new_excurtion->id]);
                }
            }
            foreach ($data['characteristics'] as $characteristic) {
                self::addCharacteristic($characteristic, $new_excurtion->id, null);
            }
            $data = $this->model::with($this->model::SHOW)->findOrFail($new_excurtion->id);
        } catch (ModelNotFoundException $error) {
            DB::rollBack();
            return response(["message" => $this->message_404, "error" => $error->getMessage()], 404);
        } catch (Exception $error) {
            DB::rollBack();
            return $error;
        }
        DB::commit();
        $message = $this->message_store_200;
        return response(compact("message", "data"));
    }

    public function addCharacteristic(array $characteristic, $new_excurtion_id = null, $characteristic_id = null)
    {
        new StoreCharacteristicRequest($characteristic);
        if (isset($characteristic['characteristic_type'])) {
            new StoreCharacteristicTypeRequest(['name' => $characteristic['characteristic_type']]);
            $characteristic['characteristic_type_id'] = CharacteristicType::firstOrCreate(['name' => $characteristic['characteristic_type']])->id;
        }
        $new_characteristic = Characteristic::create($characteristic + ['characteristic_id' => $characteristic_id]);

        if (isset($item['icon'])) {
            $link = UploadFileHelper::createFiles($item['icon']['file'], 'icons', $item['icon']['name'], '');
            Icon::create(['link' => $link] + $item['icon']['name']);
        }

        if (isset($characteristic['translables'])) {
            foreach ($characteristic['translables'] as $translable) {
                new StoreCharacteristicTranslableRequest($translable);
                CharacteristicTranslable::create($translable + ['characteristic_id' => $new_characteristic->id]);

                if (isset($translable['description'])) {
                    $description = json_decode($translable['description'], true);
                    if (!is_array($description)) {
                        continue;
                    }
                    foreach (json_decode($translable['description'], true) as $item) {
                        if (isset($item['icon'])) {
                            $link = UploadFileHelper::createFiles($item['icon']['file'], 'icons', $item['icon']['name'], '');
                            Icon::create(['link' => $link] + $item['icon']['name']);
                        }
                    }
                }
            }
        }

        ExcurtionCharacteristic::create(['characteristic_id' => $new_characteristic->id, 'excurtion_id' => $new_excurtion_id]);
        if (isset($characteristic['characteristics'])) {
            foreach ($characteristic['characteristics'] as $characteristic_new) {
                self::addCharacteristic($characteristic_new, null, $new_characteristic->id);
            }
        }
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Excurtion  $excurtion
     * @return \Illuminate\Http\Response
     */
    public function show(Excurtion $excurtion, $id)
    {
        try {
            $data = $this->model::with($this->model::SHOW)->findOrFail($id);
        } catch (ModelNotFoundException $error) {
            return response(["message" => $this->message_404], 404);
        } catch (Exception $error) {
            return response(["message" => $this->message_show_500, "error" => $error->getMessage()], 500);
        }
        // dd($data);
        $message = $this->message_show_200;
        return response(compact("message", "data"));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Excurtion  $excurtion
     * @return \Illuminate\Http\Response
     */
    public function edit(Excurtion $excurtion)
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
    public function update(UpdateExcurtionRequest $request, Excurtion $excurtion)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Excurtion  $excurtion
     * @return \Illuminate\Http\Response
     */
    public function destroy(Excurtion $excurtion)
    {
        //
    }
}
