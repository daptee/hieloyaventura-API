<?php

namespace App\Http\Controllers;

use App\Models\GeneralConfigurations;
use App\Models\Web;
use Illuminate\Http\Request;

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
        $general_configurations = GeneralConfigurations::first();

        if(!isset($general_configurations))
            $general_configurations = new GeneralConfigurations();
            
        $general_configurations->configurations = json_encode($request->configurations);
        $general_configurations->save();

        return $this->get_configurations();
    }
}
