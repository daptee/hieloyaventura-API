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
    private function validateAgency(Request $request, $permissionPath)
    {
        $agency = $request->input('authenticated_agency');

        if (!$agency) {
            return ['error' => 'Agency not authenticated', 'status' => 500];
        }

        if (!$agency->configurations) {
            return ['error' => 'Agency permissions not found', 'status' => 500];
        }

        $configurations = $agency->configurations;

        $excursion_id = $request->excursion_id ?? $request->excurtion_id;

        if (!isset($configurations[$excursion_id])) {
            return ['error' => 'Agency permissions not configured', 'status' => 403];
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
            return $response;
        } catch (\Illuminate\Http\Client\RequestException $e) {
            return response()->json($e->response->json(), $e->response->status());
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
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
        $validation = $this->validateAgency($request, 'disponibilty');
        if (isset($validation['error']))
            return response()->json(['message' => $validation['error']], $validation['status']);

        return $this->callAgencyUserController('hotels');
    }

    public function getNationalities(Request $request)
    {
        $validation = $this->validateAgency($request, 'disponibilty');
        if (isset($validation['error']))
            return response()->json(['message' => $validation['error']], $validation['status']);

        return $this->callAgencyUserController('nationalities');
    }

    public function getReservation(Request $request)
    {
        $validation = $this->validateAgency($request, 'reservations.create');
        if (isset($validation['error']))
            return response()->json(['message' => $validation['error']], $validation['status']);

        if (!$request->has('reservation_number')) {
            return response()->json(['message' => 'reservation_number parameter is required'], 400);
        }

        return $this->callAgencyUserController('ReservaxCodigo', [
            'reservation_number' => $request->reservation_number,
        ]);
    }

    public function createReservation(Request $request)
    {
        $validation = $this->validateAgency($request, 'reservations.create');
        if (isset($validation['error'])) {
            return response()->json(['message' => $validation['error']], $validation['status']);
        }

        try {
            DB::beginTransaction();

            /** ---------------------------------
             * 1️⃣ INICIAR RESERVA EN HYA
             * ---------------------------------*/
            // $startResponse = $this->callAgencyUserController(
            //     'start_reservation',
            //     [
            //         'TUR' => $request->date . '+' . $request->turn,
            //         'PSJ' => count($request->paxs_hya),
            //         'PRD' => $request->excursion_id,
            //         'TRF' => $request->has_transfer ? 'S' : 'N',
            //         'AG' => $request->agency_id,
            //     ]
            // );

            // if ($startResponse->getStatusCode() !== 200) {
            //     return $startResponse;
            // }

            // $startData = $startResponse->getData(true);
            // $reservationNumber = $startData['RSV'];
            // $reservationNumber = '1234567';

            /** ---------------------------------
             * 2️⃣ CREAR USER_RESERVATION
             * ---------------------------------*/
            $userReservationRequest = new StoreUserReservationAgencyRequest();
            // $userReservationRequest->replace(array_merge(
            //     $request->all(),
            //     [
            //         'reservation_number' => $reservationNumber,
            //         'date' => $request->date,
            //         'turn' => $request->turn,
            //     ]
            // ));
            $userReservationRequest->replace($request->all());

            $userReservationController = new \App\Http\Controllers\UserReservationController();
            $userReservationResponse = $userReservationController->store_type_agency($userReservationRequest);

            // return $userReservationResponse;
            if ($userReservationResponse->getStatusCode() !== 200) {
                DB::rollBack();
                return $userReservationResponse;
            }

            // dd($userReservationResponse);
            $data = $userReservationResponse->getData(true); // true = array
            $userReservation = $data['newUserReservation'];

            /** ---------------------------------
             * 3️⃣ CONFIRMAR RESERVA EN HYA
             * ---------------------------------*/
            // $confirmReservationResponse = $this->callAgencyUserController(
            //     'confirm_reservation',
            //     [
            //         'RSV' => $reservationNumber,
            //         'ORD' => $userReservation->id,
            //         'OBSV' => $request->confirm_data['OBSV'] ?? '',
            //         'TELEFONO' => $request->confirm_data['TELEFONO'] ?? '',
            //         'T1' => 1,
            //         'T2' => 0,
            //         'T3' => 0,
            //         'T4' => 0,
            //         'T5' => 0
            //     ]
            // );

            // if ($confirmReservationResponse->getStatusCode() !== 200) {
            //     DB::rollBack();
            //     return $confirmReservationResponse;
            // }

            /** ---------------------------------
             * 4️⃣ CONFIRMAR PASAJEROS EN HYA
             * ---------------------------------*/
            // $confirmPassengersResponse = $this->callAgencyUserController(
            //     'confirm_passengers',
            //     [
            //         'RSV' => $reservationNumber,
            //         'T1' => 1,
            //         'T2' => 0,
            //         'T3' => 0,
            //         'T4' => 0,
            //         'T5' => 0,
            //         'pasajeros' => $request->paxs_reservation,
            //     ]
            // );

            // if ($confirmPassengersResponse->getStatusCode() !== 200) {
            //     DB::rollBack();
            //     return $confirmPassengersResponse;
            // }

            /** ---------------------------------
             * 5️⃣ GUARDAR PASAJEROS INTERNOS
             * ---------------------------------*/
            $paxRequest = new StorePaxRequest();
            // $paxRequest->replace(array_merge(
            //     $request->all(),
            //     [
            //         'user_reservation_id' => $userReservation['id'],
            //         'paxs' => $request->paxs_reservation,
            //     ]
            // ));

            $paxRequest->replace([
                'user_reservation_id' => $userReservation['id'],
                'paxs' => $request->paxs_reservation,
            ]);

            $paxController = new \App\Http\Controllers\PaxController();
            $paxResponse = $paxController->store_type_agency($paxRequest);

            if ($paxResponse->getStatusCode() !== 200) {
                DB::rollBack();
                return $paxResponse;
            }

            DB::commit();

            return response()->json([
                'message' => 'Reserva creada con éxito',
                'data' => $userReservationResponse,
                // 'reservation_number' => $reservationNumber,
                // 'user_reservation_id' => $userReservation->id,
            ], 200);

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error createReservation', [
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
            ]);

            return response()->json([
                'message' => 'Error al crear la reserva',
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
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
                'agencias',
                [
                    'DESDE' => $agency_code,
                    'HASTA' => $agency_code
                ]
            );

            $agencyResponse = $agencyDataResponse->getData(true);
            $agencyName = $agencyResponse['NOMBRE'];

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
