<?php

namespace App\Http\Controllers;

use App\Models\Disease;
use App\Models\PassengerDisease;
use Illuminate\Http\Request;

class MedicalRecordController extends Controller
{
    public function diseases(Request $request)
    {
        $datos = $request->all();

        $language_id = $datos['language_id'] ?? 1;
        $diseases = Disease::where('language_id', $language_id)->get();
    
        return response()->json(['diseases' => $diseases]);
    }

    public function passenger_diseases(Request $request)
    {
        $datos = $request->all();
       
        foreach($datos as $passenger){

            $this->delete_diseases($passenger['passenger_id']);
            foreach($passenger['diseases'] as $disease){
                $passenger_disease = new PassengerDisease();
                $passenger_disease->passenger_id = $passenger['passenger_id'];
                $passenger_disease->disease_id   = $disease;
                $passenger_disease->save();
            }

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
}