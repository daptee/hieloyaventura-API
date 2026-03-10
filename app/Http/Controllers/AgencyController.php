<?php

namespace App\Http\Controllers;

use App\Mail\AgencyIntegrationWelcome;
use App\Models\Agency;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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

    public function updateSettings(Request $request)
    {
        if ($error = $this->requireAdminModule(Module::AGENCIAS)) return $error;

        try {
            $request->validate([
                'agency_code' => 'required|string',
                'email_integration_notification' => 'required|email|max:255',
            ]);

            $agency = Agency::where('agency_code', $request->agency_code)->first();

            if (!$agency) {
                return response()->json(['message' => 'Agencia no encontrada'], 404);
            }

            $agency->email_integration_notification = $request->email_integration_notification;
            $agency->save();

            return response()->json([
                'message' => 'Configuración actualizada con éxito.',
                'email_integration_notification' => $agency->email_integration_notification,
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Error en las validaciones',
                'errors' => $e->errors()
            ], 400);
        } catch (Exception $e) {
            Log::error("Error updating agency settings: " . $e->getMessage());
            return response()->json([
                'message' => 'Error al actualizar la configuración',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function sendIntegrationWelcome(Request $request)
    {
        if (Auth::user()->user_type_id != UserType::ADMIN) {
            return response()->json(['message' => 'No tiene permisos para realizar esta acción'], 403);
        }

        try {
            $request->validate([
                'agency_code' => 'required|string',
                'emails' => 'required|array|min:1',
                'emails.*' => 'required|email',
            ], [
                'emails.required' => 'Debe proporcionar al menos un correo electrónico.',
                'emails.*.email' => 'Uno o más correos electrónicos no tienen un formato válido.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Error en las validaciones',
                'errors' => $e->errors()
            ], 400);
        }

        $agency = Agency::where('agency_code', $request->agency_code)->first();

        if (!$agency) {
            return response()->json(['message' => 'Agencia no encontrada'], 404);
        }

        if (!$agency->api_key) {
            return response()->json(['message' => 'La agencia no tiene habilitada la integración por API'], 400);
        }

        // Determinar ambiente desde configuración del sistema
        $appEnvironment = config("app.environment");
        $environment = ($appEnvironment === "DEV") ? "development" : "production";

        // Fetch agency name from external HyA API using DESDE/HASTA
        $agencyName = $request->agency_code;
        try {
            $apiUrl = ($appEnvironment === "DEV")
                ? "https://apihya.hieloyaventura.com/apihya_dev"
                : "https://apihya.hieloyaventura.com/apihya";
            $agenciaResponse = Http::get("$apiUrl/Agencias", [
                'DESDE' => $request->agency_code,
                'HASTA' => $request->agency_code,
            ]);
            if ($agenciaResponse->successful()) {
                $agencias = $agenciaResponse->json();
                if (!empty($agencias[0]['NOMBRE'])) {
                    $agencyName = $agencias[0]['NOMBRE'];
                }
            }
        } catch (\Throwable $fetchError) {
            Log::warning("sendIntegrationWelcome: Error obteniendo nombre de agencia desde API externa: " . $fetchError->getMessage());
        }

        try {
            $mailable = new AgencyIntegrationWelcome($agencyName, $agency->api_key, $environment);
            foreach ($request->emails as $email) {
                Mail::to($email)->send($mailable);
            }

            return response()->json([
                'message' => 'Correo de bienvenida enviado con éxito',
                'emails_sent_to' => $request->emails,
                'agency' => $agencyName,
                'environment' => $environment,
            ], 200);
        } catch (\Throwable $th) {
            Log::error("sendIntegrationWelcome: Error enviando mail: " . $th->getMessage());
            return response()->json([
                'message' => 'Error al enviar el correo de bienvenida',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function show($agency_code)
    {
        if ($error = $this->requireAdminModule(Module::AGENCIAS)) return $error;

        try {
            $agency = Agency::where('agency_code', $agency_code)->first();

            if (!$agency) {
                return response()->json([
                    'message' => 'Agencia no encontrada'
                ], 404);
            }

            return response()->json($agency->makeVisible('api_key'), 200);
        } catch (Exception $e) {
            Log::error("Error fetching agency: " . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener la agencia',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
