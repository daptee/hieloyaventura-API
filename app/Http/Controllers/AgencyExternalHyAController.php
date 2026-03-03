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
            return ['error' => 'Agencia no autenticada', 'status' => 401];
        }

        if ($permissionPath === null) {
            return ['agency' => $agency];
        }

        if (!$agency->configurations) {
            return ['error' => 'No se encontraron permisos configurados para la agencia.', 'status' => 403];
        }

        $configurations = $agency->configurations;
        $excursion_id = $excursionId ?? $request->excursion_id ?? $request->excurtion_id;

        if (!$excursion_id) {
            return ['error' => 'El id de la excursión (excursion_id) es obligatorio.', 'status' => 400];
        }

        if (!isset($configurations[$excursion_id])) {
            return ['error' => 'La agencia no tiene permisos para esta excursión.', 'status' => 403];
        }

        $permissions = $configurations[$excursion_id];
        $keys = explode('.', $permissionPath);
        $current = $permissions;

        foreach ($keys as $key) {
            if (!isset($current[$key])) {
                return ['error' => 'No tiene permisos para realizar esta acción.', 'status' => 403];
            }
            $current = $current[$key];
        }

        if ($current !== true) {
            return ['error' => 'Acceso no autorizado para esta operación.', 'status' => 403];
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
            $originalError = $errorData['message'] ?? $errorData['ERROR_MSG'] ?? $errorData['RESULT'] ?? $e->getMessage();

            // LOG DEL ERROR ORIGINAL PARA SOPORTE INTERNO
            $this->logIntegration("Error técnico en callAgencyUserController ($method)", [
                'status' => $e->response->status(),
                'original_error' => $originalError,
                'params' => $params
            ], 'error');

            $errorMessage = $originalError;
            // Ocultar mensajes técnicos en la respuesta al cliente
            if (str_contains($errorMessage, '[FireDAC]')) {
                $errorMessage = 'No se pudo completar la operación en este momento.';
            }

            return response()->json(['message' => $errorMessage], $e->response->status());
        } catch (\Throwable $th) {
            $this->logIntegration("Excepción en callAgencyUserController ($method)", [
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine()
            ], 'critical');
            return response()->json(['message' => 'Ocurrió un error inesperado al procesar la solicitud.'], 500);
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

    public function getReservations(Request $request)
    {
        try {
            $request->validate([
                'date_from' => 'nullable|date_format:d/m/Y',
                'date_to' => 'nullable|date_format:d/m/Y',
                'reservation_number' => 'nullable|string',
            ], [
                'date_from.date_format' => 'El formato de fecha de inicio debe ser dd/mm/yyyy.',
                'date_to.date_format' => 'El formato de fecha de fin debe ser dd/mm/yyyy.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Error en las validaciones',
                'errors' => $e->errors()
            ], 400);
        }

        $agency = $request->input('authenticated_agency');
        $agency_code = $agency->agency_code;

        $params = ['AG' => (string) $agency_code];

        if ($request->filled('date_from')) {
            $params['DESDEF'] = $request->date_from;
        }
        if ($request->filled('date_to')) {
            $params['HASTAF'] = $request->date_to;
        }
        if ($request->filled('reservation_number')) {
            $params['RSV'] = (string) $request->reservation_number;
        }

        $response = $this->callAgencyUserController('reservationsAG', $params);

        if ($response->getStatusCode() !== 200) {
            return $response;
        }

        $raw = $this->extractResponseData($response);

        $data = collect($raw)->map(function ($item) {
            $paxsCant         = (int) ($item['CUANTOS'] ?? 0);
            $paxsWithTransfer = (int) ($item['CUANTOS_CON_TRANSFER'] ?? 0);

            return [
                'reservation_number' => $item['RESERVA']       ?? null,
                'turn'               => $item['TURNO']          ?? null,
                'pickup_turn'        => $item['PICKUP']         ?? null,
                'created_at'         => $item['FECHA_COMPRA']   ?? null,
                'paxs_cant'          => $paxsCant,
                'is_transfer'        => $paxsWithTransfer > 0 && $paxsWithTransfer === $paxsCant,
                'contact_name'       => $item['PAX']            ?? null,
                'contact_email'      => $item['MAIL']           ?? null,
                'contact_phone'      => $item['TELEFONO']       ?? null,
                'hotel'              => [
                    'id'   => $item['HOTEL_ID'] ?? null,
                    'name' => $item['HOTEL']    ?? null,
                ],
                'excursion_name'     => $item['PRODUCTO']       ?? null,
            ];
        })->values()->all();

        return response()->json([
            'message' => 'Listado de reservas obtenido con éxito.',
            'data'    => $data,
        ], 200);
    }

    public function updateSettings(Request $request)
    {
        try {
            $request->validate([
                'email_integration_notification' => 'required|email|max:255',
            ], [
                'email_integration_notification.required' => 'El campo email_integration_notification es obligatorio.',
                'email_integration_notification.email' => 'El valor de email_integration_notification debe ser un email válido.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Error en las validaciones',
                'errors' => $e->errors()
            ], 400);
        }

        $agency = $request->input('authenticated_agency');
        $agency->email_integration_notification = $request->email_integration_notification;
        $agency->save();

        return response()->json([
            'message' => 'Configuración actualizada con éxito.',
            'email_integration_notification' => $agency->email_integration_notification,
        ], 200);
    }

    public function getAvailability(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date_format:d/m/Y',
            'date_to' => 'required|date_format:d/m/Y',
        ], [
            'date_from.required' => 'La fecha de inicio (date_from) es obligatoria.',
            'date_from.date_format' => 'El formato de fecha de inicio debe ser dd/mm/yyyy.',
            'date_to.required' => 'La fecha de fin (date_to) es obligatoria.',
            'date_to.date_format' => 'El formato de fecha de fin debe ser dd/mm/yyyy.',
        ]);

        $validation = $this->validateAgency($request, 'disponibilty');
        if (isset($validation['error'])) {
            return response()->json(['message' => $validation['error']], $validation['status']);
        }

        return $this->callAgencyUserController('TurnosAG', [
            'FECHAD' => $request->date_from,
            'FECHAH' => $request->date_to,
            'PRD' => (string) $request->excursion_id,
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
        $unifiedNotFound = 'La reserva solicitada no fue encontrada.';

        $response = $this->callAgencyUserController('ReservaxCodigo', [
            'RSV' => (string) $reservation_number,
        ]);

        if ($response->getStatusCode() !== 200) {
            return response()->json(['message' => $unifiedNotFound], 404);
        }

        $raw = $this->extractResponseData($response);

        // Validar que la reserva pertenezca a la agencia
        if (isset($raw['AGENCIA']) && (string) $raw['AGENCIA'] !== (string) $agency_code) {
            return response()->json(['message' => $unifiedNotFound], 404);
        }

        $paxsCant         = (int) ($raw['CUANTOS'] ?? 0);
        $paxsWithTransfer = (int) ($raw['CUANTOS_CON_TRANSFER'] ?? 0);

        $pasajeros = collect($raw['PASAJEROS'] ?? [])->map(function ($pax) {
            return [
                'name'        => $pax['NOMBRE']       ?? null,
                'dni'         => $pax['DOCUMENTO']    ?? null,
                'birthdate'   => $pax['FNACIMIENTO']  ?? null,
                'nationality' => [
                    'id'   => $pax['NACIONALIDAD']  ?? null,
                    'name' => $pax['NACIONALIDADD'] ?? null,
                ],
            ];
        })->values()->all();

        $data = [
            'reservation_number' => $raw['RESERVA']      ?? null,
            'excursion_name'     => $raw['PRODUCTO']     ?? null,
            'turn'               => $raw['TURNO']        ?? null,
            'pickup_turn'        => $raw['PICKUP']       ?? null,
            'created_at'         => $raw['FECHA_COMPRA'] ?? null,
            'paxs_cant'          => $paxsCant,
            'is_transfer'        => $paxsWithTransfer > 0 && $paxsWithTransfer === $paxsCant,
            'contact_name'       => $raw['PAX']          ?? null,
            'contact_email'      => $raw['MAIL']         ?? null,
            'contact_phone'      => $raw['TELEFONO']     ?? null,
            'observations'       => $raw['OBSV']         ?? null,
            'hotel'              => [
                'id'   => $raw['HOTEL'] ?? null,
                'name' => null, // la API no retorna el nombre en esta consulta
            ],
            'paxs_information'   => $pasajeros,
        ];

        return response()->json([
            'message' => 'Reserva obtenida con éxito.',
            'data'    => $data,
        ], 200);
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
                'paxs_cant' => 'required|integer|min:1',
                'contact_name' => 'required|string|max:255',
                'contact_email' => 'required|email|max:255',
                'contact_phone' => 'required|string|max:50',
                'is_transfer' => 'required|boolean',
                'observations' => 'nullable|string',
                'paxs_information' => 'nullable|array',
            ], [
                'paxs_cant.required' => 'La cantidad de pasajeros (paxs_cant) es obligatoria.',
                'paxs_cant.integer' => 'La cantidad de pasajeros debe ser un número entero.',
                'paxs_cant.min' => 'La cantidad de pasajeros debe ser al menos 1.',
                'contact_name.required' => 'El nombre de contacto (contact_name) es obligatorio.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->logIntegration("Error de validación de entrada", $e->errors(), 'warning');
            return response()->json([
                'message' => 'Error en las validaciones',
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

        // 3. Validación de edades de pasajeros (según permiso passengers.age_validation en las configuraciones de la agencia)
        $ageValidationPermission = $this->validateAgency($request, 'passengers.age_validation');
        $shouldValidatePassengersAge = !isset($ageValidationPermission['error']);

        $paxsForAgeValidation = $request->input('paxs_information');
        if ($shouldValidatePassengersAge && !empty($paxsForAgeValidation)) {
            $excursionId = (int) $request->excursion_id;
            $ageRules = [
                1 => ['min' => 8,  'max' => 65],
                2 => ['min' => 18, 'max' => 50],
                3 => ['min' => 6,  'max' => 70],
                4 => null, // sin límite de edad
                5 => ['min' => 18, 'max' => 55],
            ];

            if (isset($ageRules[$excursionId]) && $ageRules[$excursionId] !== null) {
                $rule = $ageRules[$excursionId];
                try {
                    $excursionDate = Carbon::createFromFormat('d/m/Y', $request->date);
                } catch (\Throwable $e) {
                    $this->logIntegration("Error al parsear fecha de excursión para validación de edades", ['date' => $request->date], 'warning');
                    return response()->json(['message' => 'El formato de la fecha de la excursión no es válido.'], 400);
                }

                foreach ($paxsForAgeValidation as $index => $pax) {
                    $birthdate = $pax['birthdate'] ?? null;
                    if (!$birthdate) {
                        continue;
                    }
                    try {
                        $birthdateCarbon = Carbon::createFromFormat('d/m/Y', $birthdate);
                    } catch (\Throwable $e) {
                        try {
                            $birthdateCarbon = Carbon::createFromFormat('d/m/y', $birthdate);
                        } catch (\Throwable $_) {
                            $this->logIntegration("Error al parsear fecha de nacimiento del pasajero", ['birthdate' => $birthdate], 'warning');
                            continue;
                        }
                    }

                    $ageAtExcursion = $birthdateCarbon->diffInYears($excursionDate);

                    if ($ageAtExcursion < $rule['min'] || $ageAtExcursion > $rule['max']) {
                        $passengerName = $pax['name'] ?? "Pasajero " . ($index + 1);
                        $this->logIntegration("Validación de edad fallida", [
                            'passenger' => $passengerName,
                            'age_at_excursion' => $ageAtExcursion,
                            'rule' => $rule,
                            'excursion_id' => $excursionId,
                        ], 'warning');
                        return response()->json([
                            'message' => "La edad del pasajero \"{$passengerName}\" no es válida para esta excursión. Se requiere una edad entre {$rule['min']} y {$rule['max']} años al momento de la excursión (edad calculada: {$ageAtExcursion} años).",
                        ], 400);
                    }
                }

                $this->logIntegration("Validación de edades OK", ['excursion_id' => $excursionId]);
            }
        }

        try {
            DB::beginTransaction();

            /** ---------------------------------
             * 0️⃣ OBTENER INFO AGENCIA Y HOTEL
             * ---------------------------------*/
            $this->logIntegration("Paso 0: Obteniendo información de agencia externa", ['agency_code' => $agency_code]);

            $agenciesResponse = $this->callAgencyUserController('agencies', [
                'DESDE' => $agency_code,
                'HASTA' => $agency_code,
            ]);

            if ($agenciesResponse->getStatusCode() !== 200) {
                $errorMsg = $this->getInternalError($agenciesResponse);
                $this->logIntegration("Error en Paso 0: Error técnico al recuperar datos de agencia", [
                    'status' => $agenciesResponse->getStatusCode(),
                    'error' => $errorMsg
                ], 'error');

                return response()->json([
                    'message' => 'Hubo un problema al procesar su solicitud de reserva. Por favor, intente nuevamente más tarde.'
                ], 500);
            }

            $agenciesData = $this->extractResponseData($agenciesResponse);
            if (empty($agenciesData) || !isset($agenciesData[0]['NOMBRE'])) {
                $this->logIntegration("Error en Paso 0: Agencia no encontrada en el sistema", [
                    'agency_code' => $agency_code,
                    'response' => $agenciesData
                ], 'error');

                return response()->json([
                    'message' => 'No se pudo verificar la información de la agencia. Por favor, verifique sus credenciales.'
                ], 403);
            }

            $agency_name = $agenciesData[0]['NOMBRE'] ?? 'Agencia';
            $this->logIntegration("Paso 0 OK: Agencia encontrada", ['name' => $agency_name]);

            // Obtener nombre del hotel mediante el ID
            $this->logIntegration("Paso 0.1: Obteniendo información del hotel", ['hotel_id' => $request->hotel_id]);
            $hotelResponse = $this->callAgencyUserController('hotels');
            $hotelsData = $this->extractResponseData($hotelResponse);
            $hotel_name = null;

            if ($hotelResponse->getStatusCode() === 200 && is_array($hotelsData)) {
                $hotelFound = collect($hotelsData)->firstWhere('CODIGO', (string) $request->hotel_id);
                if ($hotelFound) {
                    $hotel_name = $hotelFound['HOTEL'];
                }
            }

            if (!$hotel_name) {
                $this->logIntegration("Error en Paso 0.1: Hotel no encontrado", ['hotel_id' => $request->hotel_id], 'error');
                return response()->json(['message' => 'El hotel seleccionado no es válido.'], 400);
            }
            $this->logIntegration("Paso 0.1 OK: Hotel encontrado", ['name' => $hotel_name]);

            // 3. Procesar pasajeros: Validar cantidad y calcular edades
            $paxsCant = (int) $request->input('paxs_cant');
            $paxs = $request->input('paxs_information');

            if ($request->has('paxs_information') && $paxs !== null) {
                if (!is_array($paxs) || count($paxs) !== $paxsCant) {
                    return response()->json([
                        'message' => 'La cantidad de pasajeros en paxs_information debe coincidir con paxs_cant.'
                    ], 400);
                }
            } else {
                $paxs = [];
            }

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
                $pax['age'] = (string) $age;
            }
            // No mergeamos de vuelta paxs_information porque el controller interno espera paxs_reservation o similar?
            // UserReservationController store_type_agency usa $request->paxs (via StorePaxRequest en Paso 4) o $request->all()?
            // Vamos a normalizar para el controller interno y externo.
            $request->merge(['paxs_reservation' => $paxs]);

            /** ---------------------------------
             * 1️⃣ INICIAR RESERVA EN HYA
             * ---------------------------------*/
            $body_array = [
                'TUR' => (string) $request->date . '+' . $request->turn,
                'PSJ' => (string) $paxsCant,
                'PRD' => (string) $request->excursion_id,
                'TRF' => $request->is_transfer ? 'S' : 'N',
                'AG' => (string) $agency_code,
                'OPERADOR' => "-1",
                'TVENTA' => "1"
            ];

            $this->logIntegration("Paso 1: Iniciando reserva", $body_array);
            $startResponse = $this->callAgencyUserController('start_reservation', $body_array);
            $startData = $this->extractResponseData($startResponse);

            $this->logIntegration("Paso 1 Response", [
                'status' => $startResponse->getStatusCode(),
                'response' => $startData
            ]);

            if ($startResponse->getStatusCode() !== 200 || (isset($startData['RESULT']) && $startData['RESULT'] === 'ERROR')) {
                DB::rollBack();
                $this->logIntegration("Error en Paso 1: Reserva rechazada", $startData, 'error');

                // Si es un error controlado (ej: falta disponibilidad), devolvemos el mensaje. 
                // Si parece un error técnico (SQL, etc), devolvemos genérico.
                $rawError = $startData['ERROR_MSG'] ?? $startData['message'] ?? 'Error desconocido';
                $userFriendlyError = str_contains($rawError, '[FireDAC]') ? 'No se pudo iniciar la reserva en este momento. Por favor, intente con otro horario o excursión.' : $rawError;

                return response()->json([
                    'message' => 'No se pudo iniciar el proceso de reserva.',
                    'error' => $userFriendlyError
                ], $startResponse->getStatusCode() === 200 ? 400 : $startResponse->getStatusCode());
            }

            $reservationNumber = $startData['RESERVA'] ?? null;
            if (!$reservationNumber) {
                $this->logIntegration("Error en Paso 1: Sin número de reserva", $startData, 'critical');
                return response()->json(['message' => 'Hubo un problema al generar el número de reserva. Por favor, contacte a soporte.'], 500);
            }

            $this->logIntegration("Paso 1 OK: Reserva iniciada", ['RSV' => $reservationNumber]);

            /** ---------------------------------
             * 2️⃣ CREAR RESERVA EN NUESTRA DB
             * ---------------------------------*/
            $this->logIntegration("Paso 2: Registrando reserva en el sistema", ['reservation_number' => $reservationNumber]);

            $userReservationRequest = new StoreUserReservationAgencyRequest();
            $userReservationRequest->replace(array_merge($request->all(), [
                'reservation_number' => $reservationNumber,
                'agency_code' => $agency_code,
                'excurtion_id' => $request->excursion_id,
                'email' => $request->contact_email,
                'phone' => $request->contact_phone,
                'full_name' => $request->contact_name,
                'hotel_name' => $hotel_name // Pasamos el nombre obtenido internamente
            ]));

            $userReservationController = new \App\Http\Controllers\UserReservationController();
            $userResResponse = $userReservationController->store_type_agency($userReservationRequest);

            if ($userResResponse->getStatusCode() !== 200) {
                DB::rollBack();
                $errorMsg = $this->getInternalError($userResResponse);
                $this->logIntegration("Error en Paso 2: Error interno de registro", [
                    'status' => $userResResponse->getStatusCode(),
                    'error' => $errorMsg
                ], 'error');

                return response()->json([
                    'message' => 'Hubo un problema técnico al registrar la reserva. Por favor, intente nuevamente.'
                ], 500);
            }

            $userReservationLocal = $this->extractResponseData($userResResponse)['newUserReservation'];
            $this->logIntegration("Paso 2 OK: Reserva registrada", ['internal_id' => $userReservationLocal['id']]);

            /** ---------------------------------
             * 3️⃣ CONFIRMACION Y CARGA DE PAXS EN HYA
             * ---------------------------------*/
            $confirmData = [
                'RSV' => (string) $reservationNumber,
                'HOTEL' => (string) $request->hotel_id,
                'PAX' => (string) $request->contact_name,
                'MAIL' => (string) $request->contact_email,
                'TELEFONO' => (string) $request->contact_phone,
                'OBSV' => (string) ($request->observations ?? ''),
                'T1' => (string) $paxsCant,
                'T2' => "0",
                'T3' => "0",
                'T4' => "0",
                'T5' => "0"
            ];

            $confirmData['pasajeros'] = $paxs;

            $this->logIntegration("Paso 3: Confirmando reserva", $confirmData);
            $confirmResponse = $this->callAgencyUserController('ConfirmaReservaAGINT', $confirmData);
            $confirmResult = $this->extractResponseData($confirmResponse);

            $this->logIntegration("Paso 3 Response", [
                'status' => $confirmResponse->getStatusCode(),
                'response' => $confirmResult
            ]);

            if ($confirmResponse->getStatusCode() !== 200 || (isset($confirmResult['RESULT']) && $confirmResult['RESULT'] === 'ERROR')) {
                DB::rollBack();
                $this->logIntegration("Error en Paso 3: Confirmación rechazada", $confirmResult, 'error');

                $rawError = $confirmResult['ERROR_MSG'] ?? $confirmResult['message'] ?? 'Error desconocido';
                // Ocultar errores técnicos de base de datos
                $userFriendlyError = str_contains($rawError, '[FireDAC]') ? 'No se pudo confirmar la reserva. Por favor, verifique los datos de los pasajeros e intente nuevamente.' : $rawError;

                return response()->json([
                    'message' => 'No se pudo confirmar la reserva en el sistema.',
                    'error' => $userFriendlyError
                ], $confirmResponse->getStatusCode() === 200 ? 400 : $confirmResponse->getStatusCode());
            }

            $this->logIntegration("Paso 3 OK: Reserva confirmada");

            /** ---------------------------------
             * 4️⃣ CONFIRMAR EN NUESTRA DB (PAXS)
             * ---------------------------------*/
            if (!empty($paxs)) {
                $this->logIntegration("Paso 4: Guardando detalle de pasajeros");

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
                    $this->logIntegration("Error en Paso 4: Error interno al guardar paxs", [
                        'status' => $paxResponse->getStatusCode(),
                        'error' => $errorMsg
                    ], 'error');

                    return response()->json([
                        'message' => 'Hubo un error al procesar el detalle de los pasajeros.'
                    ], 500);
                }
                $this->logIntegration("Paso 4 OK: Pasajeros guardados");
            } else {
                $this->logIntegration("Paso 4 SKIP: Sin pasajeros");
                $internalReservation = \App\Models\UserReservation::find($userReservationLocal['id']);
                $internalReservation->reservation_status_id = \App\Models\ReservationStatus::PAX_PENDING;
                $internalReservation->save();
                \App\Models\UserReservation::store_user_reservation_status_history(\App\Models\ReservationStatus::PAX_PENDING, $internalReservation->id);
            }


            DB::commit();
            $this->logIntegration("TRANSACCIÓN FINALIZADA CON ÉXITO");

            /** ---------------------------------
             * 5️⃣ ENVIAR MAIL
             * ---------------------------------*/
            $this->logIntegration("Paso 5: Enviando confirmación por email");
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
                'message' => 'Ocurrió un error inesperado al procesar la reserva. Por favor, contacte a soporte.'
            ], 500);
        }
    }

    public function editReservation(Request $request, $reservation_number)
    {
        $this->logIntegration("--- INICIO editReservation ---", array_merge(['reservation_number' => $reservation_number], $request->all()));

        $agency = $request->input('authenticated_agency');
        $agency_code = $agency->agency_code;
        $unifiedNotFound = 'La reserva solicitada no fue encontrada.';

        // Validar que se reciba el campo 'request' en el body
        if (!$request->has('request') || empty($request->input('request'))) {
            return response()->json(['message' => 'El campo "request" es obligatorio en el cuerpo de la solicitud.'], 400);
        }

        // Buscar reserva en DB local
        $userReservation = \App\Models\UserReservation::where('reservation_number', $reservation_number)->first();

        // Validar existencia y pertenencia
        if (!$userReservation || (string) $userReservation->agency_id !== (string) $agency_code) {
            $this->logIntegration("Error en editReservation: Reserva no encontrada o no pertenece a la agencia", ['reservation_number' => $reservation_number, 'agency_code' => $agency_code], 'warning');
            return response()->json(['message' => $unifiedNotFound], 404);
        }

        // Validar permisos de la agencia para esta excursión
        $validation = $this->validateAgency($request, 'reservations.edit', $userReservation->excurtion_id);
        if (isset($validation['error'])) {
            $this->logIntegration("Error en editReservation: Sin permisos", $validation, 'warning');
            return response()->json(['message' => 'No tiene permisos para modificar esta reserva.'], 403);
        }

        try {
            /** ---------------------------------
             * 0️⃣ OBTENER INFO AGENCIA
             * ---------------------------------*/
            $this->logIntegration("Paso 0: Obteniendo información de agencia para email", ['agency_code' => $agency_code]);
            $agencyDataResponse = $this->callAgencyUserController('agencies', ['DESDE' => $agency_code, 'HASTA' => $agency_code]);
            $agencyName = 'Agencia ' . $agency_code;

            if ($agencyDataResponse->getStatusCode() === 200) {
                $agencyResponse = $this->extractResponseData($agencyDataResponse);
                if (!empty($agencyResponse) && isset($agencyResponse[0]['NOMBRE'])) {
                    $agencyName = $agencyResponse[0]['NOMBRE'];
                }
            }
            $this->logIntegration("Paso 0 OK: Nombre de agencia obtenido", ['name' => $agencyName]);

            /** ---------------------------------
             * 1️⃣ REGISTRAR SOLICITUD DE CAMBIO
             * ---------------------------------*/
            $this->logIntegration("Paso 1: Registrando solicitud de cambio en DB local");
            \App\Models\ChangeRequest::create([
                'user_id' => null,
                'user_reservation_id' => $userReservation->id,
                'text' => $request->input('request'),
            ]);
            $this->logIntegration("Paso 1 OK: Cambio registrado");

            /** ---------------------------------
             * 2️⃣ ENVIAR MAIL DE NOTIFICACIÓN
             * ---------------------------------*/
            $this->logIntegration("Paso 2: Enviando email de notificación de cambio");
            $recipientEmail = env('RESERVATION_MODIFICATION_EMAIL', 'slarramendy@daptee.com.ar');
            Mail::to($recipientEmail)->send(new \App\Mail\AgencyReservationModification($reservation_number, $agencyName, $request->all()));
            $this->logIntegration("Paso 2 OK: Email enviado");

            $this->logIntegration("--- FIN editReservation EXITOSO ---");
            return response()->json(["message" => "¡Solicitud de modificación enviada con éxito!"], 200);
        } catch (\Throwable $th) {
            $this->logIntegration("CRITICAL ERROR en editReservation", [
                'message' => $th->getMessage(),
                'line' => $th->getLine()
            ], 'critical');
            return response()->json(["message" => "Ocurrió un error al procesar su solicitud. Por favor, intente más tarde."], 500);
        }
    }

    public function cancelReservation(Request $request, $reservation_number)
    {
        $this->logIntegration("--- INICIO cancelReservation ---", ['reservation_number' => $reservation_number]);

        $agency = $request->input('authenticated_agency');
        $unifiedNotFound = 'La reserva solicitada no fue encontrada.';

        // Buscar reserva en DB local
        $userReservation = \App\Models\UserReservation::where('reservation_number', $reservation_number)->first();

        if (!$userReservation || (string) $userReservation->agency_id !== (string) $agency->agency_code) {
            $this->logIntegration("Error en cancelReservation: Reserva no encontrada o no pertenece", ['reservation_number' => $reservation_number], 'warning');
            return response()->json(['message' => $unifiedNotFound], 404);
        }

        // Validar permisos de la agencia para esta excursión
        $validation = $this->validateAgency($request, 'reservations.cancel', $userReservation->excurtion_id);
        if (isset($validation['error'])) {
            $this->logIntegration("Error en cancelReservation: Sin permisos", $validation, 'warning');
            return response()->json(['message' => 'No tiene permisos para cancelar esta reserva.'], 403);
        }

        try {
            /** ---------------------------------
             * 1️⃣ CANCELAR EN HYA (Externo)
             * ---------------------------------*/
            $this->logIntegration("Paso 1: Cancelando reserva en sistema externo", ['RSV' => $reservation_number]);
            $response = $this->callAgencyUserController('cancel_reservation', ['RSV' => (string) $reservation_number]);
            $cancelData = $this->extractResponseData($response);

            $this->logIntegration("Paso 1 Response", [
                'status' => $response->getStatusCode(),
                'response' => $cancelData
            ]);

            if ($response->getStatusCode() !== 200 || (isset($cancelData['RESULT']) && $cancelData['RESULT'] === 'ERROR')) {
                $rawError = $cancelData['ERROR_MSG'] ?? $cancelData['message'] ?? 'Error desconocido';
                $userFriendlyError = str_contains($rawError, '[FireDAC]') ? 'No se pudo cancelar la reserva en este momento. Por favor, intente más tarde.' : $rawError;

                return response()->json([
                    'message' => 'No se pudo procesar la cancelación de la reserva.',
                    'error' => $userFriendlyError
                ], $response->getStatusCode() === 200 ? 400 : $response->getStatusCode());
            }

            $this->logIntegration("--- FIN cancelReservation EXITOSO ---");
            return response()->json(["message" => "La reserva ha sido cancelada con éxito."], 200);
        } catch (\Throwable $th) {
            $this->logIntegration("CRITICAL ERROR en cancelReservation", [
                'message' => $th->getMessage(),
                'line' => $th->getLine()
            ], 'critical');
            return response()->json(["message" => "Ocurrió un error al procesar la cancelación."], 500);
        }
    }
}
