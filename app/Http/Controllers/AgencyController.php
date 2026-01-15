<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Models\UserType;
use Illuminate\Support\Facades\Auth;

class AgencyController extends Controller
{
    public function store(Request $request)
    {
        if (Auth::user()->user_type_id != UserType::ADMIN) {
            return response()->json(['message' => 'No tiene permisos para realizar esta acción'], 403);
        }

        try {
            $request->validate([
                'agency_code' => 'required',
                'configurations' => 'required|array',
                'generate_api_key' => 'required|boolean'
            ]);

            $agency = Agency::where('agency_code', $request->agency_code)->first();

            if ($agency) {
                $agency->configurations = $request->configurations;

                if ($request->generate_api_key) {
                    if (is_null($agency->api_key)) {
                        $agency->api_key = Str::random(40);
                    }
                } else {
                    $agency->api_key = null;
                }

                $agency->save();
            } else {
                $agency = new Agency();
                $agency->agency_code = $request->agency_code;
                $agency->configurations = $request->configurations;

                if ($request->generate_api_key) {
                    $agency->api_key = Str::random(40);
                } else {
                    $agency->api_key = null;
                }

                $agency->save();
            }

            return response()->json([
                'message' => 'Agencia guardada con éxito',
                'data' => $agency
            ], 200);

        } catch (Exception $e) {
            Log::error("Error saving agency: " . $e->getMessage());
            return response()->json([
                'message' => 'Error al guardar la agencia',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($agency_code)
    {
        try {
            $agency = Agency::where('agency_code', $agency_code)->first();

            if (!$agency) {
                return response()->json([
                    'message' => 'Agencia no encontrada'
                ], 404);
            }

            return response()->json($agency, 200);
        } catch (Exception $e) {
            Log::error("Error fetching agency: " . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener la agencia',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
