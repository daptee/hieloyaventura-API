<?php

namespace App\Http\Controllers;

use App\Mail\GroupExcurtion;
use App\Models\AgencyUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class GroupExcurtionController extends Controller
{
    function group_excurtion(Request $request){
        
        $request->validate([
            'nombre_excursion'        => 'required',
            'fecha'                   => 'required',
            'turno'                   => 'required',
            'con_o_sin_traslado'      => 'required',
            'cantidad_pasajeros'      => 'required',
            'nombre_completo_persona' => 'required',
            'email_de_personal'       => 'required',
            'tel_persona'             => 'required'
        ]);
        
        $agency_user = null;
        if(isset($request->is_agency) && isset($request->agency_name) && isset(Auth::guard('agency')->user()->id)){
            $agency_user = [
                'agency_name' => $request->agency_name,
                'user_name' => Auth::guard('agency')->user()->name . ' ' . Auth::guard('agency')->user()->last_name
            ];
        }

        try {
            // Mail::to("grupos@hieloyaventura.com")->send(new GroupExcurtion($request, $agency_user, $request->file('attach_file')));
            Mail::to("reservas@hieloyaventura.com")->send(new GroupExcurtion($request, $agency_user, $request->file('attach_file')));
            return 'Mail enviado con exito!';
        } catch (\Throwable $th) {
            Log::debug(print_r([$th->getMessage(), $th->getLine()],  true));
            // return $th->getMessage();
            return 'Mail no enviado';
        }
    }
}
