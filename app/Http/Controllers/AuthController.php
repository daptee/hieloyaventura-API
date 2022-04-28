<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Log;
use JWT;
use App\Services\JwtService;
use App\Models\User;
use App\Models\Module;

class AuthController extends Controller{

    public function login(Request $request){

        $credentials = $request->only('email', 'password');
        try{
            $user = User::where('email' , $credentials['email'])->where('status',1)->get();

            if($user->count() == 0)
                return response()->json(['message' => 'Usuario y/o clave no válidos.'], 400);

            if (! $token = JWTAuth::attempt($credentials))
                return response()->json(['message' => 'Usuario y/o clave no válidos.'], 400);

        }catch (JWTException $e) {
          return response()->json(['message' => 'No fue posible crear el Token de Autenticación '], 500);
        }

        return $this->respondWithToken($token,$credentials['email']);
    }

    public function logout(){
        try{
            JWTAuth::invalidate(JWTAuth::getToken());


            return response()->json(['message' => 'Logout exitoso.']);
        }catch (JWTException $e) {

            return response()->json(['message' => $e->getMessage()])->setstatusCode(500);
        }catch(Exception $e) {

            return response()->json(['message' => $e->getMessage()])->setstatusCode(500);
        }
    }


    protected function respondWithToken($token,$email){
        $expire_in = config('jwt.ttl');
        $user  = User::where('email' , $email )->with('departments','role','concessionaire')->first();
        $modules = Module::all();

        $modules_user = [];
        foreach ($modules as $module){
            $module_user['action'] = [];
            $module_user['id'] = $module->id;
            $module_user['name'] = $module->name;
            $module_user['status'] = $module->status;
            $actions = $user->role->actions->where("module_id",$module->id);

            if($actions->count() == 0)
                continue;

            $module_user['actions'] = $actions;

            $modules_user[] = $module_user;
        }

        $data = [
            'user' => $user,
            'modules' => $modules_user
        ];

        return response()->json([
            'message' => 'Login exitoso.',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => $expire_in * 60,
            'data' => $data
        ]);
    }
}
