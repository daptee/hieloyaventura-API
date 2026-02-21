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
    private function validateAgency(Request $request, $permissionPath = null)
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

        $excursion_id = $request->excursion_id ?? $request->excurtion_id;

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
        $validation = $this->validateAgency($request, 'disponibilty');
        if (isset($validation['error']))
            return response()->json(['message' => $validation['error']], $validation['status']);

        $request->validate([
            'date_from' => 'required|date_format:d/m/Y',
            'date_to' => 'required|date_format:d/m/Y',
        ], [
            'date_from.required' => 'date_from is required',
            'date_from.date_format' => 'date_from format must be dd/mm/yyyy',
            'date_to.required' => 'date_to is required',
            'date_to.date_format' => 'date_to format must be dd/mm/yyyy',
        ]);

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

        $validation = $this->validateAgency($request, 'reservations.create');
        if (isset($validation['error'])) {
            $this->logIntegration("Error de validación de agencia", $validation, 'warning');
            return response()->json(['message' => $validation['error']], $validation['status']);
        }

        try {
            DB::beginTransaction();

            $agency_code = $validation['agency']['agency_code'];

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
                    'error' => $errorMsg,
                    'step' => 0
                ], $agenciesResponse->getStatusCode());
            }

            $agenciesData = $this->extractResponseData($agenciesResponse);
            if (empty($agenciesData) || !isset($agenciesData[0]['NOMBRE'])) {
                $this->logIntegration("Error en Paso 0: No se encontró la agencia en el sistema de H&A", [
                    'agency_code' => $agency_code,
                    'response' => $agenciesData
                ], 'error');

                return response()->json([
                    'message' => 'No se encontró información para el código de agencia indicado en el sistema externo',
                    'step' => 0
                ], 404);
            }

            $agency_name = $agenciesData[0]['NOMBRE'] ?? 'Agencia sin nombre';
            $this->logIntegration("Paso 0 OK: Agencia encontrada", ['name' => $agency_name]);

            // Procesar pasajeros: Calcular edad de cada uno
            if ($request->has('paxs_reservation') && is_array($request->paxs_reservation)) {
                $paxs = $request->paxs_reservation;
                foreach ($paxs as &$pax) {
                    if (!is_array($pax)) {
                        $this->logIntegration('Skipping invalid pax entry (not array) in createReservation', ['entry' => $pax], 'warning');
                        continue;
                    }

                    $birthdate = $pax['birthdate'] ?? null;
                    $age = 0;
                    if ($birthdate) {
                        try {
                            if (str_contains($birthdate, '/')) {
                                try {
                                    $age = Carbon::createFromFormat('d/m/Y', $birthdate)->age;
                                } catch (\Throwable $e) {
                                    $age = Carbon::createFromFormat('d/m/y', $birthdate)->age;
                                }
                            } else {
                                $age = Carbon::parse($birthdate)->age;
                            }
                        } catch (\Throwable $th) {
                            $this->logIntegration("Error al calcular edad del pasajero", ['birthdate' => $birthdate, 'error' => $th->getMessage()], 'warning');
                        }
                    }
                    $pax['age'] = $age;
                }
                // Remove any null/invalid entries to avoid downstream errors
                $paxs = array_values(array_filter($paxs, function ($item) {
                    return is_array($item) && !empty($item);
                }));

                $request->merge(['paxs_reservation' => $paxs]);
            }

            /** ---------------------------------
             * 1️⃣ INICIAR RESERVA EN HYA (Version AGINT)
             * ---------------------------------*/
            $body_array = [
                'TUR' => $request->date . '+' . $request->turn,
                'PSJ' => (int) count($request->paxs_reservation),
                'PRD' => (int) $request->excursion_id,
                'TRF' => $request->has_transfer ? 'S' : 'N',
                'AG' => $agency_code,
                'OPERADOR' => -1,
                'TVENTA' => 1
            ];

            $this->logIntegration("Paso 1: Iniciando reserva en HyA (start_reservation)", $body_array);

            $startResponse = $this->callAgencyUserController('start_reservation', $body_array);

            $startData = $this->extractResponseData($startResponse);
            $this->logIntegration("Paso 1 Response: Respuesta de HyA (start_reservation)", [
                'status' => $startResponse->getStatusCode(),
                'response' => $startData
            ]);

            if (isset($startData['RESULT']) && $startData['RESULT'] === 'ERROR') {
                $this->logIntegration("Error en Paso 1: HyA rechazó el inicio de reserva", $startData, 'error');
                return response()->json([
                    'message' => 'El sistema externo (H&A) rechazó el inicio de la reserva',
                    'error' => $startData['ERROR_MSG'] ?? 'Error desconocido',
                    'step' => 1
                ], 400);
            }

            $reservationNumber = $startData['RESERVA'] ?? null;
            if (!$reservationNumber) {
                $this->logIntegration("Error en Paso 1: No se recibió RESERVA (RSV) desde HyA", $startData, 'critical');
                return response()->json([
                    'message' => 'Error crítico: El sistema externo no devolvió un número de reserva (RESERVA)',
                    'step' => 1
                ], 500);
            }

            $this->logIntegration("Paso 1 OK: Reserva iniciada en HyA", ['RSV' => $reservationNumber]);

            /** ---------------------------------
             * 2️⃣ CREAR RESERVA EN NUESTRA DB
             * ---------------------------------*/
            $this->logIntegration("Paso 2: Registrando reserva en base de datos local");

            $userReservationRequest = new StoreUserReservationAgencyRequest();
            $userReservationRequest->replace(array_merge($request->all(), [
                'reservation_number' => $reservationNumber,
                'agency_code' => $agency_code
            ]));

            $userReservationController = new \App\Http\Controllers\UserReservationController();
            $userReservationResponse = $userReservationController->store_type_agency($userReservationRequest);

            if ($userReservationResponse->getStatusCode() !== 200) {
                DB::rollBack();
                $errorMsg = $this->getInternalError($userReservationResponse);
                $this->logIntegration("Error en Paso 2: Error al registrar en DB local", [
                    'status' => $userReservationResponse->getStatusCode(),
                    'error' => $errorMsg
                ], 'error');

                return response()->json([
                    'message' => 'Error al registrar la reserva en la base de datos local',
                    'error' => $errorMsg,
                    'step' => 2
                ], $userReservationResponse->getStatusCode());
            }

            $userReservationData = $this->extractResponseData($userReservationResponse);
            $userReservation = $userReservationData['newUserReservation'];
            $this->logIntegration("Paso 2 OK: Reserva registrada en local", ['internal_id' => $userReservation['id']]);

            /** ---------------------------------
             * 3️⃣ CONFIRMACION Y CARGA DE PAXS EN HYA (AGINT)
             * ---------------------------------*/
            $count_paxs = "0";
            if ($request->has('paxs_reservation') && !empty($request->paxs_reservation)) {
                $count_paxs = (int) count($request->paxs_reservation);
            }

            $confirmData = [
                'RSV' => (int) $reservationNumber,
                'HOTEL' => (int) $request->hotel_id,
                'PAX' => $request->pax ?? $request->contact_name,
                'MAIL' => $request->contact_email ?? $request->email,
                'T1' => $count_paxs,
                'T2' => "0",
                'T3' => "0",
                'T4' => "0",
                'T5' => "0",
                'TELEFONO' => $request->contact_phone ?? $request->phone,
                'OBSV' => $request->observations ?? $request->OBSV ?? '',
            ];

            if ($request->has('paxs_reservation') && !empty($request->paxs_reservation)) {
                $confirmData['pasajeros'] = $request->paxs_reservation;
            }

            $this->logIntegration("Paso 3: Confirmando reserva en HyA (ConfirmaReservaAGINT)", $confirmData);

            $confirmResponse = $this->callAgencyUserController('ConfirmaReservaAGINT', $confirmData);
            $confirmResult = $this->extractResponseData($confirmResponse);

            $this->logIntegration("Paso 3 Response: Respuesta de HyA (ConfirmaReservaAGINT)", [
                'status' => $confirmResponse->getStatusCode(),
                'response' => $confirmResult
            ]);

            if (isset($confirmResult['RESULT']) && $confirmResult['RESULT'] === 'ERROR') {
                DB::rollBack();
                $this->logIntegration("Error en Paso 3: HyA rechazó la confirmación", $confirmResult, 'error');
                return response()->json([
                    'message' => 'El sistema externo (H&A) rechazó la confirmación de la reserva',
                    'error' => $confirmResult['ERROR_MSG'] ?? 'Error desconocido',
                    'step' => 3
                ], 400);
            }

            $this->logIntegration("Paso 3 OK: Reserva confirmada en HyA");

            /** ---------------------------------
             * 4️⃣ CONFIRMAR EN NUESTRA DB (GUARDAR PASAJEROS INTERNOS)
             * ---------------------------------*/
            if ($request->has('paxs_reservation') && !empty($request->paxs_reservation)) {
                $this->logIntegration("Paso 4: Guardando pasajeros en base de datos local");

                $paxRequest = new StorePaxRequest();
                $paxRequest->replace(array_merge($request->all(), [
                    'user_reservation_id' => $userReservation['id'],
                    'paxs' => $request->paxs_reservation,
                ]));

                $paxController = new \App\Http\Controllers\PaxController();
                // Log payload sent to PaxController for debugging
                $this->logIntegration("Paso 4: paxRequest payload", $paxRequest->all(), 'info');

                $paxResponse = $paxController->store_type_agency($paxRequest);

                // Log paxResponse status and payload (use extractor to avoid exceptions)
                $this->logIntegration("Paso 4: paxResponse status and payload", [
                    'status' => method_exists($paxResponse, 'getStatusCode') ? $paxResponse->getStatusCode() : null,
                    'payload' => $this->extractResponseData($paxResponse)
                ], 'info');
                if ($paxResponse->getStatusCode() !== 200) {
                    DB::rollBack();
                    $errorMsg = $this->getInternalError($paxResponse);
                    $this->logIntegration("Error en Paso 4: Error al guardar paxs en local", [
                        'status' => $paxResponse->getStatusCode(),
                        'error' => $errorMsg
                    ], 'error');

                    // Log full response payload for debugging (may include trace/line)
                    $full = $this->extractResponseData($paxResponse);
                    $this->logIntegration("Paso 4: paxResponse full payload", $full, 'error');

                    return response()->json([
                        'message' => 'Error al guardar el detalle de pasajeros en la base de datos local',
                        'error' => $errorMsg,
                        'step' => 4
                    ], $paxResponse->getStatusCode());
                }
                $this->logIntegration("Paso 4 OK: Pasajeros guardados en local");
            } else {
                $this->logIntegration("Paso 4 SKIP: No hay pasajeros para guardar, seteando estado pendiente");
                $internalReservation = UserReservation::find($userReservation['id']);
                $internalReservation->reservation_status_id = ReservationStatus::PAX_PENDING;
                $internalReservation->save();

                UserReservation::store_user_reservation_status_history(ReservationStatus::PAX_PENDING, $internalReservation->id);
            }

            DB::commit();
            $this->logIntegration("TRANSACCIÓN DB COMMIT EXITOSA");

            /** ---------------------------------
             * 5️⃣ NOTIFICAR A USUARIO A TRAVES DE MAIL
             * ---------------------------------*/
            $this->logIntegration("Paso 5: Enviando email de confirmación");
            try {
                $internalRes = UserReservation::with(['status', 'excurtion', 'billing_data', 'contact_data', 'paxes', 'reservation_paxes'])->find($userReservation['id']);
                // Ensure agency_name is available to the mailable (prefer controller $agency_name)
                if (!isset($request->agency_name) && isset($agency_name)) {
                    $request->merge(['agency_name' => $agency_name]);
                }
                Mail::to($request->contact_email ?? $request->email)->send(new ConfirmationReservation($internalRes, $request));
                $this->logIntegration("Paso 5 OK: Email enviado");
            } catch (\Throwable $th) {
                $this->logIntegration("Error en Paso 5: Error al enviar mail", ['error' => $th->getMessage()], 'error');
            }

            $this->logIntegration("--- FIN createReservation EXITOSO ---", ['RSV' => $reservationNumber]);

            return response()->json([
                'message' => 'Reserva creada y confirmada con éxito',
                'reservation_number' => $reservationNumber,
                'user_reservation_id' => $userReservation['id'],
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->logIntegration("CRITICAL ERROR en createReservation", [
                'message' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile(),
                'trace' => $th->getTraceAsString()
            ], 'critical');

            return response()->json([
                'message' => 'Ocurrió un error inesperado al procesar la reserva',
                'error' => $th->getMessage(),
                'step' => 'General'
            ], 500);
        }
    }

    public function editReservation(Request $request)
    {
        // First validate agency API key only (no excursion required from client)
        $validation = $this->validateAgency($request, null);
        if (isset($validation['error']))
            return response()->json(['message' => $validation['error']], $validation['status']);

        $agencyName = null;
        try {
            // Validate that reservation_number and request text are present in the request body
            $request->validate([
                'reservation_number' => 'required',
                'request' => 'required'
            ]);

            $agency_code = $validation['agency']['agency_code'];

            // Find reservation by reservation_number to determine excursion (no need for client to send it)
            $reservation = UserReservation::where('reservation_number', $request->reservation_number)->first();
            if (!$reservation) {
                return response()->json(['message' => 'No se ha encontrado una reserva asociada al reservation_number enviado.'], 422);
            }

            // Inject excursion id into request so validateAgency can check permissions
            $request->merge(['excursion_id' => $reservation->excurtion_id ?? $reservation->excurtion_id ?? null]);

            // Now validate agency permissions for this excursion
            $validation2 = $this->validateAgency($request, 'reservations.edit');
            if (isset($validation2['error'])) {
                return response()->json(['message' => $validation2['error']], $validation2['status']);
            }

            // use the agency code from initial validation

            // eticion a api a carlos para obtener nombre de agencia
            $agencyDataResponse = $this->callAgencyUserController(
                'agencies',
                [
                    'DESDE' => $agency_code,
                    'HASTA' => $agency_code
                ]
            );

            if ($agencyDataResponse->getStatusCode() !== 200) {
                return response()->json([
                    'message' => 'Error al obtener información de la agencia en el sistema externo',
                    'error' => $this->getInternalError($agencyDataResponse),
                ], $agencyDataResponse->getStatusCode());
            }

            $agencyResponse = $this->extractResponseData($agencyDataResponse);
            if (empty($agencyResponse) || !isset($agencyResponse[0]['NOMBRE'])) {
                return response()->json([
                    'message' => 'No se encontró información para el código de agencia indicado en el sistema externo',
                ], 404);
            }
            $agencyName = $agencyResponse[0]['NOMBRE'];

            $reservation = UserReservation::where('reservation_number', $request->reservation_number)->first();
            if (!$reservation)
                return response(["message" => "No se ha encontrado una reserva asociada a reservation_number enviado."], 422);

            // Solicitud de cambio guardar en DB
            $change_request = ChangeRequest::create([
                'user_id' => null,
                'agency_code' => $agency_code,
                'user_reservation_id' => $reservation->id,
                'text' => $request->input('request'),
            ]);

            // Get the email from environment variable
            $recipientEmail = env('RESERVATION_MODIFICATION_EMAIL', 'enzo100amarilla@gmail.com');

            // Send email with all request data
            Mail::to($recipientEmail)->send(
                new \App\Mail\AgencyReservationModification(
                    $request->reservation_number,
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

    public function cancelReservation(Request $request)
    {
        $validation = $this->validateAgency($request, 'reservations.cancel');
        if (isset($validation['error']))
            return response()->json(['message' => $validation['error']], $validation['status']);

        if (!$request->has('reservation_number')) {
            return response()->json(['message' => 'reservation_number parameter is required'], 400);
        }

        return $this->callAgencyUserController('cancel_reservation', [
            'RSV' => $request->reservation_number,
        ]);
    }
}
