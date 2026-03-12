<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class AgencyJwtMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        try {
            if (!Auth::guard('agency')->check()) {
                return response()->json(['message' => 'Authorization Token not found'], 401);
            }
        } catch (TokenExpiredException $e) {
            return response()->json(['message' => 'Token is Expired'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['message' => 'Token is Invalid'], 401);
        } catch (Exception $e) {
            return response()->json(['message' => 'Authorization Token not found'], 401);
        }

        return $next($request);
    }
}
