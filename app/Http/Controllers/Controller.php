<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\UserModule;
use App\Models\UserType;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Verifica que el usuario autenticado sea ADMIN y tenga el módulo indicado.
     * Retorna una respuesta 403 si no cumple, o null si tiene permiso.
     */
    protected function requireAdminModule(int $moduleId): ?JsonResponse
    {
        $user = Auth::user() ?? JWTAuth::user();
        if ($user->user_type_id !== UserType::ADMIN) {
            return response()->json(['message' => 'No tiene permisos para realizar esta acción.'], 403);
        }
        if (!UserModule::where('user_id', $user->id)->where('module_id', $moduleId)->exists()) {
            return response()->json(['message' => 'No tiene permisos para realizar esta acción.'], 403);
        }
        return null;
    }

    /**
     * Verifica que el usuario autenticado sea ADMIN y tenga AL MENOS UNO de los módulos indicados.
     * Útil para endpoints compartidos entre secciones (ej: reservas web y reservas agencias).
     */
    protected function requireAdminAnyModule(array $moduleIds): ?JsonResponse
    {
        $user = Auth::user() ?? JWTAuth::user();
        if ($user->user_type_id !== UserType::ADMIN) {
            return response()->json(['message' => 'No tiene permisos para realizar esta acción.'], 403);
        }
        $hasAny = UserModule::where('user_id', $user->id)->whereIn('module_id', $moduleIds)->exists();
        if (!$hasAny) {
            return response()->json(['message' => 'No tiene permisos para realizar esta acción.'], 403);
        }
        return null;
    }

    /**
     * Verifica que el usuario autenticado sea ADMIN o EDITOR y tenga el módulo indicado.
     * (EDITOR tiene EXCURSIONES y CONFIGURACIONES asignados por defecto al crearse.)
     * Retorna una respuesta 403 si no cumple, o null si tiene permiso.
     */
    protected function requireModule(int $moduleId): ?JsonResponse
    {
        $user = Auth::user() ?? JWTAuth::user();
        if (!in_array($user->user_type_id, [UserType::ADMIN, UserType::EDITOR])) {
            return response()->json(['message' => 'No tiene permisos para realizar esta acción.'], 403);
        }
        if (!UserModule::where('user_id', $user->id)->where('module_id', $moduleId)->exists()) {
            return response()->json(['message' => 'No tiene permisos para realizar esta acción.'], 403);
        }
        return null;
    }
}
