<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaxRequest;
use App\Http\Requests\StoreUserReservationAgencyRequest;
use App\Http\Requests\StoreUserReservationRequest;
use App\Mail\ConfirmationReservation;
use App\Models\AgencyUser;
use App\Models\ChangeRequest;
use App\Models\PaxFile;
use App\Models\ReservationStatus;
use App\Models\UserReservation;
use App\Models\UserReservationStatusHistory;
use Auth;
use DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\Pax;
use Carbon\Carbon;

class AgencyExternalHyAController extends Controller
{
    /**
     * Validate agency permissions for a specific action
     * The agency is already authenticated by the middleware
     */
    private function validateAgency(Request $request, $permissionPath = null, $excursionId = null)
    {
        $agency = $request->input('authenticated_agency');

        if (!$agency) {
            return ['error' => 'Agency not authenticated', 'status' => 500];
        }

        // If no permission path is provided, we only validate the API key (which is already done by middleware)
        if ($permissionPath === null) {
            return ['agency' => $agency];
        }

        if (!$agency->configurations) {
            return ['error' => 'Agency permissions not found', 'status' => 500];
        }

        $configurations = $agency->configurations;

        $excursion_id = $excursionId ?? $request->excursion_id ?? $request->excurtion_id;

        if (!$excursion_id) {
            return ['error' => 'excursion_id is required', 'status' => 400];
        }

        if (!isset($configurations[$excursion_id])) {
            return ['error' => 'Agency permissions not configured for this excursion', 'status' => 403];
        }

        $permissions = $configurations[$excursion_id];

        // Check permission by path like 'disponibilty' or 'reservations.create'
        $keys = explode('.', $permissionPath);
        $current = $permissions;

        foreach ($keys as $key) {
            if (!isset($current[$key])) {
                return ['error' => "Permission '$permissionPath' not found for this agency", 'status' => 403];
            }
            $current = $current[$key];
        }

        if ($current !== true) {
            return ['error' => "Unauthorized to perform '$permissionPath'", 'status' => 403];
        }

        return ['agency' => $agency];
    }

    /**
     * Helper method to call AgencyUserController methods with consistent error handling
     */
    private function callAgencyUserController($method, $params = null)
    {
        $agencyUserController = new AgencyUserController();

        try {
            if ($params === null) {
                $response = $agencyUserController->$method();
            } else {
                $internalRequest = new Request();
                $internalRequest->replace($params);
                $response = $agencyUserController->$method($internalRequest);
            }

            if ($response instanceof \Illuminate\Http\Response || $response instanceof \Illuminate\Http\JsonResponse) {
                return $response;
            }

            return response()->json($response, 200);
        } catch (\Illuminate\Http\Client\RequestException $e) {
            $errorData = $e->response->json();
            $errorMessage = $errorData['message'] ?? $errorData['ERROR_MSG'] ?? $errorData['RESULT'] ?? $e->getMessage();

            return response()->json([
                'message' => $errorMessage
            ], $e->response->status());
        } catch (\Throwable $th) {
            Log::error("Error in AgencyExternalHyAController@callAgencyUserController: " . $th->getMessage());
            return response()->json([
                'message' => 'Ocurrió un error inesperado al procesar la solicitud.',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    private function getInternalError($response)
    {
        $data = $this->extractResponseData($response);
        if (isset($data['message']))
            return $data['message'];
        if (isset($data['error']))
            return $data['error'];
        if (isset($data['ERROR_MSG']))
            return $data['ERROR_MSG'];
        if (isset($data['RESULT']))
            return $data['RESULT'];
        return 'Error no especificado';
    }

    /**
     * Safely extract array data from various response types.
     * Returns an array when possible, or an array with a 'message' key otherwise.
     */
    private function extractResponseData($response)
    {
        if (is_array($response)) {
            return $response;
        }

        if ($response instanceof \Illuminate\Http\JsonResponse) {
            return $response->getData(true);
        }

        if ($response instanceof \Illuminate\Http\Response || is_object($response)) {
            try {
                if (method_exists($response, 'getContent')) {
                    $content = $response->getContent();
                    $decoded = json_decode($content, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        return $decoded;
                    }
                    // Try to return original payload if available
                    if (property_exists($response, 'original') && is_array($response->original)) {
                        return $response->original;
                    }
                    return ['message' => $content];
                }

                if (method_exists($response, 'toArray')) {
                    return $response->toArray();
                }

                return (array) $response;
            } catch (\Throwable $th) {
                return ['message' => $th->getMessage()];
            }
        }

        if (is_string($response)) {
            $decoded = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
            return ['message' => $response];
        }

        return [];
    }

    /**
     * Helper to log integration details to a dedicated channel
     */
    private function logIntegration($message, $details = [], $level = 'info')
    {
        try {
            Log::channel('agency_integration')->$level($message, $details);
        } catch (\Throwable $th) {
            // Fallback to default log if custom channel fails
            Log::$level("Integration Log Error: " . $message, $details);
        }
    }

    public function getAvailability(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date_format:d/m/Y',
            'date_to' => 'required|date_format:d/m/Y',
        ], [
            'date_from.required' => 'date_from is required',
            'date_from.date_format' => 'date_from format must be dd/mm/yyyy',
            'date_to.required' => 'date_to is required',
            'date_to.date_format' => 'date_to format must be dd/mm/yyyy',
        ]);

        $validation = $this->validateAgency($request, 'disponibilty');
        if (isset($validation['error']))
            return response()->json(['message' => $validation['error']], $validation['status']);

        return $this->callAgencyUserController('TurnosAG', [
            'FECHAD' => $request->date_from,
            'FECHAH' => $request->date_to,
            'PRD' => $request->excursion_id,
        ]);
    }

    public function getHotels()
    {
        return $this->callAgencyUserController('hotels');
    }

    public function getNationalities()
    {
        return $this->callAgencyUserController('nationalities');
    }

    public function getReservation(Request $request, $reservation_number)
    {
        $agency = $request->input('authenticated_agency');
        $agency_code = $agency->agency_code;

        $response = $this->callAgencyUserController('ReservaxCodigo', [
            'RSV' => $reservation_number,
        ]);

        $unifiedMessage = 'The requested reservation was not found.';

        if ($response->getStatusCode() === 200) {
            $data = $this->extractResponseData($response);

            // Validar que la reserva pertenezca a la agencia
            if (isset($data['AGENCIA']) && (string) $data['AGENCIA'] !== (string) $agency_code) {
                return response()->json(['message' => $unifiedMessage], 404);
            }
        } else {
            // Si la API interna devuelve error (usualmente 400 o 404 cuando no existe), unificamos el mensaje
            return response()->json(['message' => $unifiedMessage], 404);
        }

        return $response;
    }

    public function createReservation(Request $request)
    {
        $this->logIntegration("--- INICIO createReservation ---", $request->all());

        // 1. Validaciones de entrada estrictas
        try {
            $request->validate([
                'excursion_id' => 'required|integer',
                'date' => 'required|date_format:d/m/Y',
                'turn' => 'required|date_format:H:i',
                'hotel_id' => 'required|integer',
                'hotel_name' => 'required|string|max:255',
                'pax' => 'required|string|max:255',
                'contact_email' => 'required|email|max:255',
                'contact_phone' => 'required|string|max:50',
                'is_transfer' => 'required|boolean',
                'observations' => 'nullable|string',
                'paxs_reservation' => 'nullable|array',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->logIntegration("Error de validación de entrada", $e->errors(), 'warning');
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 400);
        }

        // 2. Validar permisos de la agencia para esta excursión
        $validation = $this->validateAgency($request, 'reservations.create');
        if (isset($validation['error'])) {
            $this->logIntegration("Error de validación de agencia", $validation, 'warning');
            return response()->json(['message' => $validation['error']], $validation['status']);
        }

        $agency = $validation['agency'];
        $agency_code = $agency->agency_code;

        try {
            DB::beginTransaction();

            /** ---------------------------------
             * 0️⃣ OBTENER INFO AGENCIA (Externo)
             * ---------------------------------*/
            $this->logIntegration("Paso 0: Obteniendo información de agencia externa", ['agency_code' => $agency_code]);

            $agenciesResponse = $this->callAgencyUserController('agencies', [
                'DESDE' => $agency_code,
                'HASTA' => $agency_code,
            ]);

            if ($agenciesResponse->getStatusCode() !== 200) {
                $errorMsg = $this->getInternalError($agenciesResponse);
                $this->logIntegration("Error en Paso 0: Error al obtener info de agencia externa", [
                    'status' => $agenciesResponse->getStatusCode(),
                    'error' => $errorMsg
                ], 'error');

                return response()->json([
                    'message' => 'Error al obtener información de la agencia en el sistema externo',
                    'error' => $errorMsg
                ], $agenciesResponse->getStatusCode());
            }

            $agenciesData = $this->extractResponseData($agenciesResponse);
            if (empty($agenciesData) || !isset($agenciesData[0]['NOMBRE'])) {
                $this->logIntegration("Error en Paso 0: No se encontró la agencia en el sistema de H&A", [
                    'agency_code' => $agency_code,
                    'response' => $agenciesData
                ], 'error');

                return response()->json([
                    'message' => 'No se encontró información para el código de agencia indicado en el sistema externo'
                ], 404);
            }

            $agency_name = $agenciesData[0]['NOMBRE'] ?? 'Agencia sin nombre';
            $this->logIntegration("Paso 0 OK: Agencia encontrada", ['name' => $agency_name]);

            // 3. Procesar pasajeros: Calcular edad de cada uno de forma robusta
            $paxs = $request->input('paxs_reservation', []);
            foreach ($paxs as &$pax) {
                $age = 0;
                $birthdate = $pax['birthdate'] ?? null;
                if ($birthdate) {
                    try {
                        $age = Carbon::createFromFormat('d/m/Y', $birthdate)->age;
                    } catch (\Throwable $th) {
                        try {
                            $age = Carbon::createFromFormat('d/m/y', $birthdate)->age;
                        } catch (\Throwable $e) {
                            $this->logIntegration("Error al calcular edad del pasajero", ['birthdate' => $birthdate, 'error' => $e->getMessage()], 'warning');
                        }
                    }
                }
                $pax['age'] = $age;
            }
            $request->merge(['paxs_reservation' => $paxs]);

            /** ---------------------------------
             * 1️⃣ INICIAR RESERVA EN HYA
             * ---------------------------------*/
            $body_array = [
                'TUR' => $request->date . '+' . $request->turn,
                'PSJ' => count($paxs),
                'PRD' => (int) $request->excursion_id,
                'TRF' => $request->is_transfer ? 'S' : 'N',
                'AG' => $agency_code,
                'OPERADOR' => -1,
                'TVENTA' => 1
            ];

            $this->logIntegration("Paso 1: Iniciando reserva en HyA", $body_array);
            $startResponse = $this->callAgencyUserController('start_reservation', $body_array);
            $startData = $this->extractResponseData($startResponse);

            $this->logIntegration("Paso 1 Response: Respuesta de HyA", [
                'status' => $startResponse->getStatusCode(),
                'response' => $startData
            ]);

            if ($startResponse->getStatusCode() !== 200 || (isset($startData['RESULT']) && $startData['RESULT'] === 'ERROR')) {
                $this->logIntegration("Error en Paso 1: HyA rechazó el inicio de reserva", $startData, 'error');
                return response()->json([
                    'message' => 'El sistema externo (H&A) rechazó el inicio de la reserva',
                    'error' => $startData['ERROR_MSG'] ?? $startData['message'] ?? 'Error desconocido'
                ], $startResponse->getStatusCode() === 200 ? 400 : $startResponse->getStatusCode());
            }

            $reservationNumber = $startData['RESERVA'] ?? null;
            if (!$reservationNumber) {
                $this->logIntegration("Error en Paso 1: No se recibió RESERVA desde HyA", $startData, 'critical');
                return response()->json(['message' => 'Error crítico: El sistema externo no devolvió un número de reserva'], 500);
            }

            $this->logIntegration("Paso 1 OK: Reserva iniciada en HyA", ['RSV' => $reservationNumber]);

            /** ---------------------------------
             * 2️⃣ CREAR RESERVA EN NUESTRA DB
             * ---------------------------------*/
            $this->logIntegration("Paso 2: Registrando reserva local", ['reservation_number' => $reservationNumber]);

            $userReservationRequest = new StoreUserReservationAgencyRequest();
            $userReservationRequest->replace(array_merge($request->all(), [
                'reservation_number' => $reservationNumber,
                'agency_code' => $agency_code,
                'excurtion_id' => $request->excursion_id,
                'email' => $request->contact_email,
                'phone' => $request->contact_phone,
                'full_name' => $request->pax
            ]));

            $userReservationController = new \App\Http\Controllers\UserReservationController();
            $userResResponse = $userReservationController->store_type_agency($userReservationRequest);

            if ($userResResponse->getStatusCode() !== 200) {
                DB::rollBack();
                $errorMsg = $this->getInternalError($userResResponse);
                $this->logIntegration("Error en Paso 2: Error al registrar en DB local", [
                    'status' => $userResResponse->getStatusCode(),
                    'error' => $errorMsg
                ], 'error');

                return response()->json([
                    'message' => 'Error al registrar la reserva en la base de datos local',
                    'error' => $errorMsg
                ], $userResResponse->getStatusCode());
            }

            $userReservationLocal = $this->extractResponseData($userResResponse)['newUserReservation'];
            $this->logIntegration("Paso 2 OK: Reserva registrada en local", ['internal_id' => $userReservationLocal['id']]);

            /** ---------------------------------
             * 3️⃣ CONFIRMACION Y CARGA DE PAXS EN HYA
             * ---------------------------------*/
            $confirmData = [
                'RSV' => (int) $reservationNumber,
                'HOTEL' => (int) $request->hotel_id,
                'PAX' => $request->pax,
                'MAIL' => $request->contact_email,
                'TELEFONO' => $request->contact_phone,
                'OBSV' => $request->observations ?? '',
                'T1' => count($paxs),
                'T2' => "0",
                'T3' => "0",
                'T4' => "0",
                'T5' => "0",
                'pasajeros' => $paxs
            ];

            $this->logIntegration("Paso 3: Confirmando reserva en HyA", $confirmData);
            $confirmResponse = $this->callAgencyUserController('ConfirmaReservaAGINT', $confirmData);
            $confirmResult = $this->extractResponseData($confirmResponse);

            $this->logIntegration("Paso 3 Response: Respuesta de HyA", [
                'status' => $confirmResponse->getStatusCode(),
                'response' => $confirmResult
            ]);

            if ($confirmResponse->getStatusCode() !== 200 || (isset($confirmResult['RESULT']) && $confirmResult['RESULT'] === 'ERROR')) {
                DB::rollBack();
                $this->logIntegration("Error en Paso 3: HyA rechazó la confirmación", $confirmResult, 'error');
                return response()->json([
                    'message' => 'El sistema externo (H&A) rechazó la confirmación de la reserva',
                    'error' => $confirmResult['ERROR_MSG'] ?? $confirmResult['message'] ?? 'Error desconocido'
                ], $confirmResponse->getStatusCode() === 200 ? 400 : $confirmResponse->getStatusCode());
            }

            $this->logIntegration("Paso 3 OK: Reserva confirmada en HyA");

            /** ---------------------------------
             * 4️⃣ CONFIRMAR EN NUESTRA DB (PAXS)
             * ---------------------------------*/
            if (!empty($paxs)) {
                $this->logIntegration("Paso 4: Guardando pasajeros en base de datos local");

                $paxRequest = new StorePaxRequest();
                $paxRequest->replace([
                    'user_reservation_id' => $userReservationLocal['id'],
                    'paxs' => $paxs,
                ]);

                $paxController = new \App\Http\Controllers\PaxController();
                $paxResponse = $paxController->store_type_agency($paxRequest);

                if ($paxResponse->getStatusCode() !== 200) {
                    DB::rollBack();
                    $errorMsg = $this->getInternalError($paxResponse);
                    $this->logIntegration("Error en Paso 4: Error al guardar paxs en local", [
                        'status' => $paxResponse->getStatusCode(),
                        'error' => $errorMsg
                    ], 'error');

                    return response()->json([
                        'message' => 'Error al guardar el detalle de pasajeros en la base de datos local',
                        'error' => $errorMsg
                    ], $paxResponse->getStatusCode());
                }
                $this->logIntegration("Paso 4 OK: Pasajeros guardados en local");
            } else {
                $this->logIntegration("Paso 4 SKIP: No hay pasajeros para guardar, seteando estado pendiente");
                $internalReservation = \App\Models\UserReservation::find($userReservationLocal['id']);
                $internalReservation->reservation_status_id = \App\Models\ReservationStatus::PAX_PENDING;
                $internalReservation->save();
                \App\Models\UserReservation::store_user_reservation_status_history(\App\Models\ReservationStatus::PAX_PENDING, $internalReservation->id);
            }


            DB::commit();
            $this->logIntegration("TRANSACCIÓN DB COMMIT EXITOSA");

            /** ---------------------------------
             * 5️⃣ ENVIAR MAIL
             * ---------------------------------*/
            $this->logIntegration("Paso 5: Enviando email de confirmación");
            try {
                $internalRes = \App\Models\UserReservation::with(['status', 'excurtion', 'billing_data', 'contact_data', 'paxes', 'reservation_paxes'])->find($userReservationLocal['id']);
                $request->merge(['agency_name' => $agency_name]);
                Mail::to($request->contact_email)->send(new ConfirmationReservation($internalRes, $request));
                $this->logIntegration("Paso 5 OK: Email enviado");
            } catch (\Throwable $th) {
                $this->logIntegration("Error enviando mail", ['error' => $th->getMessage()], 'error');
            }

            $this->logIntegration("--- FIN createReservation EXITOSO ---", ['RSV' => $reservationNumber]);

            return response()->json([
                'message' => 'Reserva creada y confirmada con éxito',
                'reservation_number' => $reservationNumber,
                'user_reservation_id' => $userReservationLocal['id'],
            ], 200);

        } catch (\Throwable $th) {
            DB::rollBack();
            $this->logIntegration("CRITICAL ERROR en createReservation", [
                'message' => $th->getMessage(),
                'line' => $th->getLine(),
                'trace' => $th->getTraceAsString()
            ], 'critical');

            return response()->json([
                'message' => 'Ocurrió un error inesperado al procesar la reserva',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function editReservation(Request $request, $reservation_number)
    {
        $agency = $request->input('authenticated_agency');
        $agency_code = $agency->agency_code;
        $unifiedMessage = 'The requested reservation was not found.';

        // Validar que se reciba el campo 'request' en el body
        if (!$request->has('request') || empty($request->input('request'))) {
            return response()->json(['message' => 'The request field is required in the body.'], 400);
        }

        // Buscar reserva en DB local
        $userReservation = \App\Models\UserReservation::where('reservation_number', $reservation_number)->first();

        // Validar existencia y pertenencia
        if (!$userReservation || (string) $userReservation->agency_id !== (string) $agency_code) {
            return response()->json(['message' => $unifiedMessage], 404);
        }

        // Validar permisos de la agencia para esta excursión
        $validation = $this->validateAgency($request, 'reservations.edit', $userReservation->excurtion_id);
        if (isset($validation['error'])) {
            return response()->json(['message' => $validation['error']], $validation['status']);
        }

        try {
            // Obtener información de la agencia (especialmente el nombre para el mail)
            $agencyDataResponse = $this->callAgencyUserController(
                'agencies',
                [
                    'DESDE' => $agency_code,
                    'HASTA' => $agency_code
                ]
            );

            $agencyName = 'Agencia ' . $agency_code;

            if ($agencyDataResponse->getStatusCode() === 200) {
                $agencyResponse = $this->extractResponseData($agencyDataResponse);
                if (!empty($agencyResponse) && isset($agencyResponse[0]['NOMBRE'])) {
                    $agencyName = $agencyResponse[0]['NOMBRE'];
                }
            }

            // Solicitud de cambio guardar en DB
            \App\Models\ChangeRequest::create([
                'user_id' => null,
                'user_reservation_id' => $userReservation->id,
                'text' => $request->input('request'),
            ]);

            // Mail
            $recipientEmail = env('RESERVATION_MODIFICATION_EMAIL', 'slarramendy@daptee.com.ar');

            Mail::to($recipientEmail)->send(
                new \App\Mail\AgencyReservationModification(
                    $reservation_number,
                    $agencyName,
                    $request->all()
                )
            );

            return response()->json(["message" => "Solicitud de modificación enviada con éxito!"], 200);
        } catch (\Throwable $th) {
            Log::error("Error in AgencyExternalHyAController@editReservation: " . $th->getMessage());
            return response()->json(["message" => "Error al procesar la solicitud", "error" => $th->getMessage()], 500);
        }
    }

    public function cancelReservation(Request $request, $reservation_number)
    {
        $agency = $request->input('authenticated_agency');
        $unifiedMessage = 'The requested reservation was not found.';

        // Buscar reserva en DB local para obtener excursion_id y validar pertenencia
        $userReservation = \App\Models\UserReservation::where('reservation_number', $reservation_number)->first();

        if (!$userReservation || (string) $userReservation->agency_id !== (string) $agency->agency_code) {
            return response()->json(['message' => $unifiedMessage], 404);
        }

        // Validar permisos de la agencia para esta excursión
        $validation = $this->validateAgency($request, 'reservations.cancel', $userReservation->excurtion_id);
        if (isset($validation['error'])) {
            return response()->json(['message' => $validation['error']], $validation['status']);
        }

        return $this->callAgencyUserController('cancel_reservation', [
            'RSV' => $reservation_number,
        ]);
    }
}
