<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserPasswordRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Mail\recoverPasswordMailable;
use App\Models\Module;
use App\Models\User;
use App\Models\UserModule;
use App\Models\UserType;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{

    public function index(Request $request)
    {
        $users = User::with(['user_type', 'language', 'modules.module'])->get();
        // try {
        //     $data = $this->model::with($this->model::INDEX);
        //     foreach ($request->all() as $key => $value) {
        //         if (method_exists($this->model, 'scope' . $key)) {
        //             $data->$key($value);
        //         }
        //     }
        //     $data = $this->model::with($this->model::INDEX)->get();
        // } catch (ModelNotFoundException $error) {
        //     return response(["message" => $this->message_404], 404);
        // } catch (Exception $error) {
        //     return response(["message" => $this->message_show_500, "error" => $error->getMessage()], 500);
        // }
        // $message = $this->message_show_500;
        return response(compact("users", $users));
    }

    // public function store(StoreUserRequest $request)
    // {
        // $message = "Error al crear en la {$this->s}.";
        // $data = $request->all();

        // $new = new $this->model($data);
        // try {
        //     $new->save();
        //     $data = $this->model::with($this->model::SHOW)->findOrFail($new->id);
        // } catch (ModelNotFoundException $error) {
        //     return response(["message" => $this->message_404, "error" => $error->getMessage()], 404);
        // } catch (Exception $error) {
        //     return response(["message" => $this->message_store_500, "error" => $error->getMessage()], 500);
        // }
        // $message = $this->message_show_200;
        // return response(compact("message", "data"));
    // }

    public function store(Request $request)
    {
        $request->validate([
            "name" => 'required',
            "email" => 'required|unique:users',
            "password" => 'required',
        ]);

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->user_type_id = $request->user_type_id;
        $user->dni = $request->dni;
        $user->birth_date = $request->birthdate;
        $user->phone = $request->phone;
        $user->nationality_id = $request->nationality_id;
        $user->save();

        if($request->user_type_id == UserType::ADMIN){
            foreach ($request->modules as $module) {
                $user_module = new UserModule();
                $user_module->user_id = $user->id;
                $user_module->module_id = $module;
                $user_module->save();
            }
        }
        
        $user = User::getAllDataUser($user->user_type_id, $user->id);

        return response(compact(
            "user", $user
        ));
    }

    public function authenticate(Request $request)
    {
        // $credentials = $request->only('email', 'password');
        // try {
        //     if (!$token = JWTAuth::attempt($credentials)) {
        //         return response()->json(['error' => 'invalid_credentials'], 400);
        //     }
        // } catch (JWTException $e) {
        //     return response()->json(['error' => 'could_not_create_token'], 500);
        // }
        // return response()->json(compact('token'));
    }

    public function getAuthenticatedUser()
    {
        // try {
        //     if (!$user = JWTAuth::parseToken()->authenticate()) {
        //         return response()->json(['user_not_found'], 404);
        //     }
        // } catch (TokenExpiredException $e) {
        //     return response()->json(['token_expired'], $e->getStatusCode());
        // } catch (TokenInvalidException $e) {
        //     return response()->json(['token_invalid'], $e->getStatusCode());
        // } catch (JWTException $e) {
        //     return response()->json(['token_absent'], $e->getStatusCode());
        // }
        // return response()->json(compact('user'));
    }

    public function update(UpdateUserRequest $request)
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

    public function update_admin(Request $request, $id)
    {
        $user = User::find($id);

        if(!isset($user))
            return response()->json(['message' => 'Usuario no valido.'], 400);

        $user->name = $request->name;
        $user->email = $request->email;

        if($request->password)
            $user->password = Hash::make($request->password); // consultar seba antes de subir

        $user->dni = $request->dni;
        $user->birth_date = $request->birthdate;
        $user->phone = $request->phone;
        $user->nationality_id = $request->nationality_id;

        if($user->user_type_id == UserType::ADMIN){
            
            if($request->modules){
                UserModule::where('user_id', $id)->delete();
                
                foreach ($request->modules as $module) {
                    $user_module = new UserModule();
                    $user_module->user_id = $user->id;
                    $user_module->module_id = $module;
                    $user_module->save();
                }
            }

        }
        
        $user->save();

        $user = User::getAllDataUser($user->user_type_id, $user->id);

        return response(compact(
            "user", $user
        ));

    }

    public function updatePassword(UpdateUserPasswordRequest $request)
    {
        $user = auth()->user();

        $credentials = [
            'email' => $user->email, 
            'password' => $request->current_password
        ];

        try{
            if ($token = JWTAuth::attempt($credentials)) {
                DB::beginTransaction();
                    $new_password_hashed = Hash::make($request->new_password);
                    $user->password = $new_password_hashed;

                    $user->save();
                DB::commit();
            } else {
                return response()->json(['message' => 'Contraseña actual no válida.'], 422);
            }

        } catch(Exception $e) {
            DB::rollBack();
            Log::error(print_r($e->getMessage(), true));
        }

        return response()->json([
            'message' => 'La contraseña se actualizó con éxito', 
            'user' => $user,
            'token' => $token
        ]);
    }

    public function recover_password_user(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if(!$user)
            return response()->json(['message' => 'No existe un usuario con el mail solicitado.'], 402);
        
        try {
            $new_password = Str::random(16);
            $user->password = Hash::make($new_password);
            $user->save();
            
            $data = [
                'name' => $user->nombre,
                'email' => $user->email,
                'password' => $new_password,
            ];
            Mail::to($user->email)->send(new recoverPasswordMailable($data));
        } catch (Exception $error) {
            return response(["error" => $error->getMessage()], 500);
        }
       
        return response()->json(['message' => 'Correo enviado con exito.'], 200);
        
    }

    public function get_modules()
    {
        return response()->json(['modules' => Module::all()]);
    }
}
