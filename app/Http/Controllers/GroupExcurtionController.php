<?php

namespace App\Http\Controllers;

use App\Mail\GroupExcurtion;
use Illuminate\Http\Request;
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

        try {
            Mail::to("grupos@hieloyaventura.com")->send(new GroupExcurtion($request));
            return 'Mail enviado con exito!';
        } catch (\Throwable $th) {
            Log::debug(print_r([$th->getMessage(), $th->getLine()],  true));
            // return $th->getMessage();
            return 'Mail no enviado';
        }
    }
}
