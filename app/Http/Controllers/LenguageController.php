<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLenguageRequest;
use App\Http\Requests\UpdateLenguageRequest;
use App\Models\Lenguage;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class LenguageController extends Controller
{
    public $model = Lenguage::class;
    public $s = "lenguaje"; //sustantivo singular
    public $sp = "lenguajes"; //sustantivo plural
    public $ss = "lenguaje/s"; //sustantivo sigular/plural
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
}
