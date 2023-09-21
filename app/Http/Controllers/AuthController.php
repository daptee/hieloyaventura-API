<?php

namespace App\Http\Controllers;

use App\Models\AgencyUser;
use App\Models\AgencyUserType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
// use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Log;
use JWT;
use App\Services\JwtService;
use App\Models\User;
use App\Models\Module;
use App\Models\UserType;
use Faker\Provider\UserAgent;
use Illuminate\Support\Facades\Session;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller{

    public function login(Request $request){

        $credentials = $request->only('email', 'password');
        try{
            $user = User::where('email' , $credentials['email'])->get();

            if($user->count() == 0)
                return response()->json(['message' => 'Usuario y/o clave no válidos.'], 400);

            if (! $token = JWTAuth::attempt($credentials))
                return response()->json(['message' => 'Usuario y/o clave no válidos.'], 400);

        }catch (JWTException $e) {
          return response()->json(['message' => 'No fue posible crear el Token de Autenticación '], 500);
        }

// Session::put('applocale', $request);
        return $this->respondWithToken($token,Auth::user()->user_type_id, Auth::user()->id);
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


    protected function respondWithToken($token,$type_user_id,$id){
        $expire_in = config('jwt.ttl');
        // $user  = User::where('email' , $email )->first();
        $user = User::getAllDataUser($type_user_id, $id);
        // $modules = Module::all();

        // $modules_user = [];
        // foreach ($modules as $module){
        //     $module_user['action'] = [];
        //     $module_user['id'] = $module->id;
        //     $module_user['name'] = $module->name;
        //     $module_user['status'] = $module->status;
        //     $actions = $user->role->actions->where("module_id",$module->id);

        //     if($actions->count() == 0)
        //         continue;

        //     $module_user['actions'] = $actions;

        //     $modules_user[] = $module_user;
        // }

        $data = [
            'user' => $user,
            // 'modules' => $modules_user
        ];

        return response()->json([
            'message' => 'Login exitoso.',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => $expire_in * 60,
            'data' => $data
        ]);
    }

    protected function respondWithTokenAgency($token, $id){
        $expire_in = config('jwt.ttl');
        $user = AgencyUser::getAllDataUser($id);
        $data = [
            'user' => $user,
        ];

        return response()->json([
            'message' => 'Login exitoso.',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => $expire_in * 60,
            'data' => $data
        ]);
    }

    public function login_admin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // User admin 
        $user_to_validate = User::where('email', $request->email)->first();
        
        if(!isset($user_to_validate) || $user_to_validate->user_type_id == UserType::CLIENTE)
            return response()->json(['message' => 'Email no existente o usuario no admin.'], 400);
        
        $credentials = $request->only('email', 'password');

        if (! $token = JWTAuth::attempt($credentials))
            return response()->json(['message' => 'Email y/o clave no válidos.'], 400);

        // if (!Auth::attempt($credentials)) 
            // return response()->json(['message' => 'El usuario o la contraseña son invalidos.'], 400);
        
        // $token = Auth::user()->createToken('auth_token')->plainTextToken;

        return $this->respondWithToken($token,Auth::user()->user_type_id, Auth::user()->id);
    }

    public function login_agency_user(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // User agency 
        $user_to_validate = AgencyUser::where('email', $request->email)->first();

        if(!isset($user_to_validate) || !in_array($user_to_validate->agency_user_type_id, [AgencyUserType::ADMIN, AgencyUserType::VENDEDOR]) || $user_to_validate->active == 0)
            return response()->json(['message' => 'Email y/o clave no válidos.'], 400);
        
        $credentials = $request->only('email', 'password');

        if (! $token = Auth::guard('agency')->attempt($credentials))
            return response()->json(['message' => 'Email y/o clave no válidos.'], 400);

        return $this->respondWithTokenAgency($token, $user_to_validate->id);
    }
}
