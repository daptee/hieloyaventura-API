<?php

namespace App\Http\Controllers;

use App\Mail\AgencyOtpMailable;
use App\Models\AgencyUser;
use App\Models\AgencyUserType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
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
            $user = User::where('email', $credentials['email'] ?? '')->get();

            if($user->count() == 0) {
                $this->logFailedLogin('web', $request, 'email no encontrado');
                return response()->json(['message' => 'Usuario y/o clave no válidos.'], 400);
            }

            if ($user->first()->password_expired) {
                return response()->json([
                    'message'          => 'Usuario y/o clave no válidos.',
                    'password_expired' => true,
                ], 400);
            }

            if (! $token = JWTAuth::attempt($credentials)) {
                $this->logFailedLogin('web', $request, 'contraseña incorrecta');
                return response()->json(['message' => 'Usuario y/o clave no válidos.'], 400);
            }

        }catch (JWTException $e) {
          return response()->json(['message' => 'No fue posible crear el Token de Autenticación '], 500);
        }

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

        $user_to_validate = User::where('email', $request->email)->first();

        if(!isset($user_to_validate) || $user_to_validate->user_type_id == UserType::CLIENTE) {
            $this->logFailedLogin('admin', $request, 'email no encontrado o usuario no admin');
            return response()->json(['message' => 'Email no existente o usuario no admin.'], 400);
        }

        if ($user_to_validate->password_expired) {
            return response()->json([
                'message'          => 'Email y/o clave no válidos.',
                'password_expired' => true,
            ], 400);
        }

        $credentials = $request->only('email', 'password');

        if (! $token = JWTAuth::attempt($credentials)) {
            $this->logFailedLogin('admin', $request, 'contraseña incorrecta');
            return response()->json(['message' => 'Email y/o clave no válidos.'], 400);
        }

        return $this->respondWithToken($token,Auth::user()->user_type_id, Auth::user()->id);
    }

    /**
     * Paso 1 del login de agencias: valida credenciales y envía OTP por email.
     * El JWT se entrega recién en verify_agency_otp().
     */
    public function login_agency_user(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user_to_validate = AgencyUser::where('email', $request->email)->first();

        if (!isset($user_to_validate) || $user_to_validate->active == 0) {
            $this->logFailedLogin('agency', $request, 'email no encontrado o usuario inactivo');
            return response()->json(['message' => 'Email y/o clave no válidos.'], 400);
        }

        if ($user_to_validate->password_expired) {
            return response()->json([
                'message'          => 'Email y/o clave no válidos.',
                'password_expired' => true,
            ], 400);
        }

        $credentials = $request->only('email', 'password');

        if (!Auth::guard('agency')->attempt($credentials)) {
            $this->logFailedLogin('agency', $request, 'contraseña incorrecta');
            return response()->json(['message' => 'Email y/o clave no válidos.'], 400);
        }

        // Credenciales correctas — cerrar sesión temporal
        Auth::guard('agency')->logout();

        // Emitir OTP

        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $user_to_validate->otp_code       = $otp;
        $user_to_validate->otp_expires_at = now()->addMinutes(10);
        $user_to_validate->save();

        Mail::to($user_to_validate->email)->send(new AgencyOtpMailable($otp, 'login'));

        return response()->json([
            'message'     => 'Se envió un código de verificación a su correo electrónico.',
            'pending_2fa' => true,
        ]);
    }

    /**
     * Paso 2 del login de agencias: valida el OTP y entrega el JWT.
     */
    public function verify_agency_otp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp'   => 'required|string|size:6',
        ]);

        $user = AgencyUser::where('email', $request->email)
                          ->where('active', 1)
                          ->first();

        if (!$user || !$user->otp_code || !$user->otp_expires_at) {
            return response()->json(['message' => 'Código inválido o expirado.'], 400);
        }

        if ($user->otp_expires_at < now()) {
            $user->otp_code       = null;
            $user->otp_expires_at = null;
            $user->save();
            return response()->json(['message' => 'El código ha expirado. Iniciá sesión nuevamente.'], 400);
        }

        if (!hash_equals($user->otp_code, $request->otp)) {
            $this->logFailedLogin('agency-otp', $request, 'código OTP incorrecto');
            return response()->json(['message' => 'Código inválido.'], 400);
        }

        // OTP correcto — limpiar y emitir JWT
        $user->otp_code       = null;
        $user->otp_expires_at = null;
        $user->save();

        $token = Auth::guard('agency')->login($user);

        return $this->respondWithTokenAgency($token, $user->id);
    }

    private function logFailedLogin(string $type, Request $request, string $reason): void
    {
        $logPath = storage_path('logs/security/failed-logins-' . now()->format('Y-m') . '.log');
        $logger = Log::build([
            'driver' => 'single',
            'path'   => $logPath,
            'level'  => 'warning',
        ]);
        $logger->warning('Login fallido', [
            'type'       => $type,
            'reason'     => $reason,
            'email'      => $request->input('email'),
            'ip'         => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp'  => now()->toDateTimeString(),
        ]);
    }
}
