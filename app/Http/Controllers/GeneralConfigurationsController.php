<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Models\GeneralConfigurations;
use App\Models\Web;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GeneralConfigurationsController extends Controller
{
    public function get_configurations()
    {
        $general_configurations = GeneralConfigurations::first();
        
        $general_configurations->configurations = json_decode($general_configurations->configurations);

        return response()->json([ 'general_configurations' => $general_configurations ]);
    }

    public function store(Request $request)
    {
        if(!isset($request->configurations))
            return response()->json([ 'message' => "No se puede guardar 'configurations' vacio."], 400);

        $general_configurations = GeneralConfigurations::first();

        if(!isset($general_configurations))
            $general_configurations = new GeneralConfigurations();

        $keysDB = count(array_keys((array)json_decode($general_configurations->configurations)));
        $keysNewJson = count(array_keys((array)$request->configurations));
        
        if ($keysNewJson < $keysDB) 
            return response()->json([ 'message' => "Verificar datos faltantes."], 400);

        $general_configurations->configurations = json_encode($request->configurations);
        $general_configurations->save();

        dd(Auth::user());
        $audit = new Audit();
        $audit->id_user = Auth::user()->id;
        $audit->action = json_encode(["action" => "general configuration updated"]);
        $audit->save();

        return $this->get_configurations();
    }
}
