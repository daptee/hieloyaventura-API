<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public $model = CharacteristicType::class;
    public $s = "usuarios"; //sustantivo singular
    public $sp = "usuarios"; //sustantivo plural
    public $ss = "usuario/s"; //sustantivo sigular/plural
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

    public function store(StoreUserRequest $request)
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
    public function authenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 400);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }
        return response()->json(compact('token'));
    }
    public function getAuthenticatedUser()
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }
        } catch (TokenExpiredException $e) {
            return response()->json(['token_expired'], $e->getStatusCode());
        } catch (TokenInvalidException $e) {
            return response()->json(['token_invalid'], $e->getStatusCode());
        } catch (JWTException $e) {
            return response()->json(['token_absent'], $e->getStatusCode());
        }
        return response()->json(compact('user'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $datos = $request->only([
            "name",
            "email",
            "nationality_id",
            "dni",
            "phone"
            // "lenguage_id",
            // "birth_date",
        ]);

        $user = $user->fill($datos);

        try {
            DB::beginTransaction();
                $user->save();
            DB::commit();
        } catch (\Throwable $th) {
            Log::debug(print_r([$th->getMessage(), $th->getLine()],  true));
            return response(["message" => "Error en el servidor al actualizar los datos del usuario", "error" => "UCU0001"], 500);
        }

        return response()->json($user);
    }
}
