<?php

namespace App\Http\Controllers;

use App\Mail\MedicalRecordMailable;
use App\Models\Disease;
use Illuminate\Support\Facades\Mail;
use App\Models\PassengerDisease;
use App\Models\Pax;
use App\Models\UserReservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class MedicalRecordController extends Controller
{
    public function diseases($language_id)
    {
        $diseases = Disease::where('language_id', $language_id ?? 1)->get();
    
        return response()->json(['diseases' => $diseases]);
    }

    public function passenger_diseases(Request $request, $hash_reservation_number, $mail_to)
    {
        $datos = $request->all();
        $passengers_diseases = [];
        $reservation_number = Crypt::decryptString($hash_reservation_number);
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
    
        // foreach($passengers_diseases as $passenger){
        //     $diseases = PassengerDisease::with(['disease'])->where('passenger_id', $passenger['id'])->get();
            
        //     foreach ($diseases as $disease) {
        //         $passenger['diseases'][] = [ 'nombre' => $disease->disease->nombre ];
        //         // return $passenger;
        //     }
        // }

        // return $passengers_diseases;

        Mail::to('info@hieloyaventura.com')->send(new MedicalRecordMailable($mailto, $passengers_diseases, $reservation_number));

        return response()->json(['message' => "Guardado con exito!"]);
    }

    public function delete_diseases($id)
    {
        $diseases = PassengerDisease::where('passenger_id', $id)->get();

        foreach($diseases as $disease){
            $disease->delete();
        }
    }
}
