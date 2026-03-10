<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdminOrAgencyMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Intentar autenticar como admin (guard por defecto, tabla users)
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if ($user) {
                return $next($request);
            }
        } catch (TokenExpiredException $e) {
            return response()->json(['status' => 'Token is Expired'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['status' => 'Token is Invalid'], 401);
        } catch (JWTException $e) {
            // No hay token en el request
            return response()->json(['status' => 'Authorization Token not found'], 401);
        } catch (Exception $e) {
            // El token es válido pero el usuario no existe en la tabla users.
            // Puede ser un token de agencia; se intenta con el guard agency.
        }

        // Intentar autenticar como usuario de agencia (guard agency, tabla agency_users)
        try {
            if (Auth::guard('agency')->check()) {
                return $next($request);
            }
        } catch (TokenExpiredException $e) {
            return response()->json(['status' => 'Token is Expired'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['status' => 'Token is Invalid'], 401);
        } catch (Exception $e) {
            // ignore
        }

        return response()->json(['status' => 'Authorization Token not found'], 401);
    }
}
