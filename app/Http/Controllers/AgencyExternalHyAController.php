<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\GeneralConfigurations;
use App\Models\UserReservation;
use App\Models\AgencyUser;
use App\Models\ChangeRequest;
use App\Models\ChangeRequestFile;
use App\Mail\ReservationRequestChange;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AgencyExternalHyAController extends Controller
{
    private function get_url()
    {
        $environment = config("app.environment");
        if ($environment == "DEV") {
            return "https://apihya.hieloyaventura.com/apihya_dev";
        } else {
            return "https://apihya.hieloyaventura.com/apihya";
        }
    }

    private function validateAgency(Request $request, $permissionPath)
    {
        $apiKey = $request->header('X-API-KEY') ?? $request->input('api_key');

        if (!$apiKey) {
            return ['error' => 'API Key is required', 'status' => 401];
        }

        $agency = Agency::where('api_key', $apiKey)->first();

        if (!$agency) {
            return ['error' => 'Invalid API Key', 'status' => 401];
        }

        $generalConfig = GeneralConfigurations::first();
        if (!$generalConfig) {
            return ['error' => 'General configurations not found', 'status' => 500];
        }

        $configurations = json_decode($generalConfig->configurations, true);
        $agencyId = (string) $agency->id;

        if (!isset($configurations[$agencyId])) {
            return ['error' => 'Agency permissions not configured', 'status' => 403];
        }

        $permissions = $configurations[$agencyId];

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

    public function getAvailability(Request $request)
    {
        $validation = $this->validateAgency($request, 'disponibilty');
        if (isset($validation['error']))
            return response()->json(['message' => $validation['error']], $validation['status']);

        $url = $this->get_url();
        $response = Http::get("$url/Turnos", $request->all());

        if ($response->successful()) {
            return $response->json();
        } else {
            return response()->json($response->json(), $response->status());
        }
    }

    public function getHotels(Request $request)
    {
        // Hoteles and Nationalities might not have explicit permissions in the example, 
        // but we'll check 'disponibilty' as a base or just allow if agency is valid.
        $validation = $this->validateAgency($request, 'disponibilty');
        if (isset($validation['error']))
            return response()->json(['message' => $validation['error']], $validation['status']);

        $url = $this->get_url();
        $response = Http::get("$url/Hoteles");

        if ($response->successful()) {
            return $response->json();
        } else {
            return response()->json($response->json(), $response->status());
        }
    }

    public function getNationalities(Request $request)
    {
        $validation = $this->validateAgency($request, 'disponibilty');
        if (isset($validation['error']))
            return response()->json(['message' => $validation['error']], $validation['status']);

        $url = $this->get_url();
        $response = Http::get("$url/Naciones");

        if ($response->successful()) {
            return $response->json();
        } else {
            return response()->json($response->json(), $response->status());
        }
    }

    public function getReservation(Request $request)
    {
        $validation = $this->validateAgency($request, 'reservations.create'); // Or a generic 'reservations.view' if it existed, but using create for now
        if (isset($validation['error']))
            return response()->json(['message' => $validation['error']], $validation['status']);

        if (!$request->has('RSV')) {
            return response()->json(['message' => 'RSV parameter is required'], 400);
        }

        $url = $this->get_url();
        $response = Http::get("$url/ReservaxCodigo", ['RSV' => $request->RSV]);

        if ($response->successful()) {
            return $response->json();
        } else {
            return response()->json($response->json(), $response->status());
        }
    }

    public function createReservation(Request $request)
    {
        $validation = $this->validateAgency($request, 'reservations.create');
        if (isset($validation['error']))
            return response()->json(['message' => $validation['error']], $validation['status']);

        $url = $this->get_url();
        $response = Http::post("$url/IniciaReserva", $request->all());

        if ($response->successful()) {
            return $response->json();
        } else {
            return response()->json($response->json(), $response->status());
        }
    }

    public function editReservation(Request $request)
    {
        $validation = $this->validateAgency($request, 'reservations.edit');
        if (isset($validation['error']))
            return response()->json(['message' => $validation['error']], $validation['status']);

        $agency = $validation['agency'];

        try {
            $request->validate([
                'reservation_number' => 'required',
                'request' => 'required',
                'attachments' => 'nullable|array',
            ]);

            $reservation = UserReservation::where('reservation_number', $request->reservation_number)->first();

            if (!$reservation) {
                return response()->json(["message" => "No se ha encontrado una reserva asociada a reservation_number enviado."], 422);
            }

            // Find a user for this agency to associate with the change request
            $user = AgencyUser::where('agency_code', $agency->agency_code)->first();

            DB::beginTransaction();

            $change_request = ChangeRequest::create([
                'user_id' => $user ? $user->id : null,
                'user_reservation_id' => $reservation->id,
                'text' => $request->input('request'),
            ]);

            $storedFiles = [];

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $fileName = uniqid() . '_' . $file->getClientOriginalName();
                    $file->move(public_path('change_requests'), $fileName);
                    $path = 'change_requests/' . $fileName;

                    $storedFiles[] = public_path($path);

                    ChangeRequestFile::create([
                        'change_request_id' => $change_request->id,
                        'path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                    ]);
                }
            }

            // Prepare pseudo-user if none found
            if (!$user) {
                $user = (object) [
                    'name' => 'Agency External API',
                    'last_name' => $agency->agency_code,
                    'email' => null
                ];
            }

            Mail::to("reservas@hieloyaventura.com")->send(
                new ReservationRequestChange($request->all(), $user, $storedFiles)
            );

            DB::commit();

            return response()->json(["message" => "Solicitud de edición enviada con éxito!"], 200);

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error("Error in AgencyExternalHyAController@editReservation: " . $th->getMessage());
            return response()->json(["message" => "Error al procesar la solicitud", "error" => $th->getMessage()], 500);
        }
    }

    public function cancelReservation(Request $request)
    {
        $validation = $this->validateAgency($request, 'reservations.cancel');
        if (isset($validation['error']))
            return response()->json(['message' => $validation['error']], $validation['status']);

        if (!$request->has('RSV')) {
            return response()->json(['message' => 'RSV parameter is required'], 400);
        }

        $url = $this->get_url();
        $response = Http::asForm()->post("$url/CancelaReserva", [
            'RSV' => $request->RSV
        ]);

        if ($response->successful()) {
            return $response->json();
        } else {
            return response()->json($response->json(), $response->status());
        }
    }
}
