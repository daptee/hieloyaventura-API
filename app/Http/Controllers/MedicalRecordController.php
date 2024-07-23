<?php

namespace App\Http\Controllers;

use App\Mail\MedicalRecordExternalMailable;
use App\Mail\MedicalRecordMailable;
use App\Models\AuditReservation;
use App\Models\Disease;
use App\Models\MedicalRecord;
use App\Models\Nationality;
use Illuminate\Support\Facades\Mail;
use App\Models\PassengerDisease;
use App\Models\Pax;
use App\Models\User;
use App\Models\UserReservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\DB;

class MedicalRecordController extends Controller
{
    public function diseases($language_id)
    {
        $diseases = Disease::where('language_id', $language_id ?? 1)->get();
    
        return response()->json(['diseases' => $diseases]);
    }

    public function passenger_diseases(Request $request, $hash_reservation_number, $mail_to)
    {
        try {
            DB::beginTransaction();
               
                $datos = $request->all();
                $passengers_diseases = [];
                $reservation_number = Crypt::decryptString($hash_reservation_number);
                $user_reservation = UserReservation::where('reservation_number', $reservation_number)->first();

                if($user_reservation){
                    AuditReservation::store_audit_reservation($user_reservation->id, ["operation" => "Carga ficha medica", "status" => "Ok"]);
                }else{
                    return response()->json([ 'message' => "Reserva inexistente."], 400);
                }

                $mailto = $mail_to;
            
                foreach($datos as $passenger){

                    $this->delete_diseases($passenger['passenger_id']);

                    $pax = Pax::find($passenger['passenger_id']); 

                    if(isset($pax)){
                        $pax->age         = $passenger['age'];
                        $pax->blood_type  = $passenger['blood_type'];
                        $pax->description = $passenger['description'];
                        $pax->save();
                    }

                    if(count($passenger['diseases'])){
                        foreach($passenger['diseases'] as $disease){
                            $passenger_disease = new PassengerDisease();
                            $passenger_disease->passenger_id = $passenger['passenger_id'];
                            $passenger_disease->disease_id   = $disease;
                            $passenger_disease->save();
                        }
                    }
                    
                    $diseases = PassengerDisease::with(['disease'])->where('passenger_id', $passenger['passenger_id'])->get();
                    $diseases_passenger = [];
                    foreach ($diseases as $disease) {
                        $diseases_passenger[] = $disease->disease->nombre;
                    }

                    $passengers_diseases[] = [
                        'id' => $pax->id,
                        'passenger_name' => $pax->name,
                        'diseases' => $diseases_passenger
                    ];
                }

                $medical_record = new MedicalRecord();
                $medical_record->order_number = $reservation_number;
                $medical_record->excurtion_date = $user_reservation->date ?? null;
                $medical_record->passengers = json_encode($datos);
                $medical_record->save();

            DB::commit();

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::debug(print_r([$th->getMessage() . " - error en proceso de carga 'passenger_diseases'", $th->getLine()],  true));
            
            if($user_reservation)
                AuditReservation::store_audit_reservation($user_reservation->id, ["operation" => "Carga ficha medica", "status" => "Error"]);
            
            return response(["message" => "error en proceso de carga 'passenger_diseases'", "error" => $th->getMessage(), "line" => $th->getLine()], 500);
        }

        try {
            Mail::to('info@hieloyaventura.com')->send(new MedicalRecordMailable($mailto, $passengers_diseases, $reservation_number));
        } catch (Exception $error) {
            Log::debug(print_r([$error->getMessage() . " error en envio de mail a info@hieloyaventura.com en 'passenger diseases'", $error->getLine()],  true));
            // return response(["message" => "error en envio de mail a info@hieloyaventura.com en 'passenger diseases'", "error" => $error->getMessage()], 600);
        }

        return response()->json(['message' => "Guardado con exito!"]);
    }

    public function delete_diseases($id)
    {
        $diseases = PassengerDisease::where('passenger_id', $id)->get();

        foreach($diseases as $disease){
            $disease->delete();
        }
    }

    public function medical_record(Request $request)
    {
        $request->validate([
            'order_number' => 'required',
            'excurtion_date' => 'required',
            'passengers' => 'required'
        ]);

        $medical_record = new MedicalRecord();
        $medical_record->order_number = $request->order_number;
        $medical_record->excurtion_date = $request->excurtion_date;
        $medical_record->passengers = json_encode($request->passengers);
        $medical_record->save();

        try {
            Mail::to('info@hieloyaventura.com')->send(new MedicalRecordExternalMailable("info@hieloyaventura.com", $medical_record, $request->order_number));
        } catch (Exception $error) {
            Log::debug(print_r([$error->getMessage() . " error en envio de mail a cliente con voucher", $error->getLine()],  true));
            return response(["message" => "error en envio de mail a cliente con voucher", "error" => $error->getMessage()], 600);
        }

        return response()->json(['medical_record' => $this->getAllMedicalRecord($medical_record)]);
    }

    public function getAllMedicalRecord($medical_record)
    {
        $medical_record->passengers = json_decode($medical_record->passengers);
        return $medical_record;
    }
}
