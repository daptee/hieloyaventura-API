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
            return ['error' => 'excursion_id is required for this action', 'status' => 400];
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
            return response()->json([
                'message' => 'Error en la petición externa',
                'error' => $errorData['message'] ?? $errorData['ERROR_MSG'] ?? $errorData['RESULT'] ?? $e->getMessage(),
                'details' => $errorData
            ], $e->response->status());
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile()
            ], 500);
        }
    }

    private function getInternalError($response)
    {
        $data = $response->getData(true);
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

    public function getAvailability(Request $request)
    {
        $validation = $this->validateAgency($request, 'disponibilty');
        if (isset($validation['error']))
            return response()->json(['message' => $validation['error']], $validation['status']);

        return $this->callAgencyUserController('TurnosAG', [
            'FECHAD' => $request->date_from,
            'FECHAH' => $request->date_to,
            'PRD' => $request->excursion_id,
        ]);
    }

    public function getHotels(Request $request)
    {
        // solo validar api key
        $validation = $this->validateAgency($request, null);
        if (isset($validation['error']))
            return response()->json(['message' => $validation['error']], $validation['status']);

        return $this->callAgencyUserController('hotels');
    }

    public function getNationalities(Request $request)
    {
        // quitar validacion
        $validation = $this->validateAgency($request, null);
        if (isset($validation['error']))
            return response()->json(['message' => $validation['error']], $validation['status']);

        return $this->callAgencyUserController('nationalities');
    }

    public function getReservation(Request $request)
    {
        // quitar validacion excursion id.
        // validar que la reserva sea de la agencia.
        // en caso de aplicar eso decir que no encuentra la reserva
        $validation = $this->validateAgency($request, 'reservations.show');
        if (isset($validation['error']))
            return response()->json(['message' => $validation['error']], $validation['status']);

        if (!$request->has('reservation_number')) {
            return response()->json(['message' => 'reservation_number parameter is required'], 400);
        }

        $agency_code = $validation['agency']['agency_code'];

        $response = $this->callAgencyUserController('ReservaxCodigo', [
            'RSV' => $request->reservation_number,
        ]);

        if ($response->getStatusCode() === 200) {
            $data = $response->getData(true);

            // Validar que la reserva pertenezca a la agencia
            // Dependiendo de la estructura de ReservaxCodigo, comparamos con AG o similar
            if (isset($data['AG']) && (string) $data['AG'] !== (string) $agency_code) {
                return response()->json(['message' => 'No se encontró la reserva solicitada para esta agencia'], 404);
            }
        }

        return $response;
    }

    public function createReservation(Request $request)
    {
        $validation = $this->validateAgency($request, 'reservations.create');
        if (isset($validation['error'])) {
            return response()->json(['message' => $validation['error']], $validation['status']);
        }

        try {
            DB::beginTransaction();

            $agency_code = $validation['agency']['agency_code'];

            $agenciesResponse = $this->callAgencyUserController('agencies', [
                'DESDE' => $agency_code,
                'HASTA' => $agency_code,
            ]);

            if ($agenciesResponse->getStatusCode() !== 200) {
                return response()->json([
                    'message' => 'Error al obtener información de la agencia en el sistema externo',
                    'error' => $this->getInternalError($agenciesResponse),
                    'step' => 0
                ], $agenciesResponse->getStatusCode());
            }

            $agenciesData = $agenciesResponse->getData(true);
            if (empty($agenciesData) || !isset($agenciesData[0]['NOMBRE'])) {
                return response()->json([
                    'message' => 'No se encontró información para el código de agencia indicado en el sistema externo',
                    'step' => 0
                ], 404);
            }

            $agency_name = $agenciesData[0]['NOMBRE'] ?? 'Agencia sin nombre';

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

            Log::debug('body enviado a inicia reserva', $body_array);

            $startResponse = $this->callAgencyUserController(
                'start_reservation',
                [
                    'TUR' => $request->date . '+' . $request->turn,
                    'PSJ' => (int) count($request->paxs_reservation),
                    'PRD' => (int) $request->excursion_id,
                    'TRF' => $request->has_transfer ? 'S' : 'N',
                    'AG' => $agency_code,
                    'OPERADOR' => -1,
                    'TVENTA' => 1
                ]
            );

            // if ($startResponse->getStatusCode() !== 200) {
            //     return response()->json([
            //         'message' => 'Error al iniciar la reserva en el sistema externo (H&A)',
            //         'error' => $this->getInternalError($startResponse),
            //         'step' => 1
            //     ], $startResponse->getStatusCode());
            // }

            Log::debug('Inicia reserva response', $startResponse->getData(true));

            $startData = $startResponse->getData(true);

            if (isset($startData['RESULT']) && $startData['RESULT'] === 'ERROR') {
                return response()->json([
                    'message' => 'El sistema externo (H&A) rechazó el inicio de la reserva',
                    'error' => $startData['ERROR_MSG'] ?? 'Error desconocido',
                    'step' => 1
                ], 400);
            }

            $reservationNumber = $startData['RESERVA'] ?? null;
            if (!$reservationNumber) {
                return response()->json([
                    'message' => 'Error crítico: El sistema externo no devolvió un número de reserva (RESERVA)',
                    'step' => 1
                ], 500);
            }

            /** ---------------------------------
             * 2️⃣ CREAR RESERVA EN NUESTRA DB
             * ---------------------------------*/
            $userReservationRequest = new StoreUserReservationAgencyRequest();
            $userReservationRequest->replace(array_merge($request->all(), [
                'reservation_number' => $reservationNumber,
                'agency_code' => $agency_code
            ]));

            Log::debug('user reservation request', $userReservationRequest->all());

            $userReservationController = new \App\Http\Controllers\UserReservationController();
            $userReservationResponse = $userReservationController->store_type_agency($userReservationRequest);

            if ($userReservationResponse->getStatusCode() !== 200) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Error al registrar la reserva en la base de datos local',
                    'error' => $this->getInternalError($userReservationResponse),
                    'step' => 2
                ], $userReservationResponse->getStatusCode());
            }

            $userReservationData = $userReservationResponse->getData(true);
            $userReservation = $userReservationData['newUserReservation'];

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
                'PAX' => (int) $request->pax ?? $request->contact_name,
                'MAIL' => $request->contact_email ?? $request->email,
                'T1' => $count_paxs,
                'T2' => "0",
                'T3' => "0",
                'T4' => "0",
                'T5' => "0",
                'TELEFONO' => (int) $request->contact_phone ?? $request->phone,
                'OBSV' => $request->observations ?? $request->OBSV ?? '',
            ];

            // Pasajeros son opcionales
            if ($request->has('paxs_reservation') && !empty($request->paxs_reservation)) {
                $confirmData['pasajeros'] = $request->paxs_reservation;
            }

            Log::debug('body enviado a confirmar reserva', $confirmData);

            $confirmResponse = $this->callAgencyUserController('ConfirmaReservaAGINT', $confirmData);

            // if ($confirmResponse->getStatusCode() !== 200) {
            //     DB::rollBack();
            //     return response()->json([
            //         'message' => 'Error al confirmar la reserva en el sistema externo (H&A)',
            //         'error' => $this->getInternalError($confirmResponse),
            //         'step' => 3
            //     ], $confirmResponse->getStatusCode());
            // }

            $confirmResult = $confirmResponse->getData(true);

            Log::debug('Confirma reserva response', $confirmResult);

            if (isset($confirmResult['RESULT']) && $confirmResult['RESULT'] === 'ERROR') {
                DB::rollBack();
                return response()->json([
                    'message' => 'El sistema externo (H&A) rechazó la confirmación de la reserva',
                    'error' => $confirmResult['ERROR_MSG'] ?? 'Error desconocido',
                    'step' => 3
                ], 400);
            }

            /** ---------------------------------
             * 4️⃣ CONFIRMAR EN NUESTRA DB (GUARDAR PASAJEROS INTERNOS)
             * ---------------------------------*/
            if ($request->has('paxs_reservation') && !empty($request->paxs_reservation)) {
                $paxRequest = new StorePaxRequest();
                $paxRequest->replace([
                    'user_reservation_id' => $userReservation['id'],
                    'paxs' => $request->paxs_reservation,
                ]);

                $paxController = new \App\Http\Controllers\PaxController();
                $paxResponse = $paxController->store_type_agency($paxRequest);

                if ($paxResponse->getStatusCode() !== 200) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Error al guardar el detalle de pasajeros en la base de datos local',
                        'error' => $this->getInternalError($paxResponse),
                        'step' => 4
                    ], $paxResponse->getStatusCode());
                }
            } else {
                // Si no hay pasajeros, movemos el status de la reserva a iniciado o pendiente de paxs
                $internalReservation = UserReservation::find($userReservation['id']);
                $internalReservation->reservation_status_id = ReservationStatus::PAX_PENDING;
                $internalReservation->save();

                UserReservation::store_user_reservation_status_history(ReservationStatus::PAX_PENDING, $internalReservation->id);
            }

            DB::commit();

            /** ---------------------------------
             * 5️⃣ NOTIFICAR A USUARIO A TRAVES DE MAIL
             * ---------------------------------*/
            try {
                $internalRes = UserReservation::with(['status', 'excurtion', 'billing_data', 'contact_data', 'paxes', 'reservation_paxes'])->find($userReservation['id']);
                Mail::to($request->contact_email ?? $request->email)->send(new ConfirmationReservation($internalRes, $request));
            } catch (\Throwable $th) {
                Log::error('Error enviando mail de creacion/confirmation de reserva', ['error' => $th->getMessage()]);
            }

            return response()->json([
                'message' => 'Reserva creada y confirmada con éxito',
                'reservation_number' => $reservationNumber,
                'user_reservation_id' => $userReservation['id'],
            ], 200);

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error crítico en createReservation', [
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile()
            ]);

            return response()->json([
                'message' => 'Ocurrió un error inesperado al procesar la reserva',
                'error' => $th->getMessage(),
                'step' => 'General'
            ], 500);
        }
    }

    public function editReservation(Request $request)
    {
        $validation = $this->validateAgency($request, 'reservations.edit');
        if (isset($validation['error']))
            return response()->json(['message' => $validation['error']], $validation['status']);

        $agencyName = null;
        try {
            // Validate that reservation_number is present
            $request->validate([
                'reservation_number' => 'required',
                'request' => 'required'
            ]);

            $agency_code = $validation['agency']['agency_code'];

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

            $agencyResponse = $agencyDataResponse->getData(true);
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
