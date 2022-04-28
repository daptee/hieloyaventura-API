<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFaqRequest;
use App\Http\Requests\UpdateFaqRequest;
use App\Models\Faq;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public $model = Faq::class;
    public $s = "pregunta"; //sustantivo singular
    public $sp = "preguntas"; //sustantivo plural
    public $ss = "pregunta/s"; //sustantivo sigular/plural
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

    public function store(StoreFaqRequest $request)
    {
        $message = "Error al crear en la {$this->s}.";
        $data = $request->all();

        $data = new $this->model($data);
        try {
            $data->save();
            $data = $this->model::with($this->model::SHOW)->findOrFail($data->id);
        } catch (ModelNotFoundException $error) {
            return response(["message" => "No se encontraron {$this->prp} {$this->sp}.", "error" => $error->getMessage()], 404);
        } catch (Exception $error) {
            return response(compact("message", $error->getMessage()), 500);
        }
        $message = "Se ha creado {$this->pr} {$this->s} correctamente.";
        return response(compact("message", "data"));
    }
}
