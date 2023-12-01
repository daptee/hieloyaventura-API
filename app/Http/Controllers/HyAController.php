<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class HyAController extends Controller
{
    public function passengers_types(Request $request)
    {
        $leng = $request->leng ?? 'ES';
        $response = Http::get("https://apihya.hieloyaventura.com/apihya/TiposPasajeros?LENG=$leng");   
        return $response->json();
    }

    public function nationalities()
    {
        $response = Http::get('https://apihya.hieloyaventura.com/apihya/Naciones');   
        return $response->json();
    }

    public function hotels()
    {
        $response = Http::get('https://apihya.hieloyaventura.com/apihya/Hoteles');   
        return $response->json();
    }
 
    public function rates()
    {
        $response = Http::get('https://apihya.hieloyaventura.com/apihya/Tarifas');   
        return $response->json();
    }
    
    public function excursions(Request $request)
    {
        $response = Http::get("https://apihya.hieloyaventura.com/apihya/Productos?FECHA=$request->date");   
        return $response->json();
    }

    public function shifts(Request $request)
    {
        $fecha_desde = $request->date_from;
        $fecha_hasta = $request->date_to;
        $excursion_id = $request->excursion_id;
        $response = Http::get("https://apihya.hieloyaventura.com/apihya/Turnos?FECHAD=$fecha_desde&FECHAH=$fecha_hasta&PRD=$excursion_id");   
        return $response->json();
    }

    public function ReservaxCodigo(Request $request)
    {
        $response = Http::get("https://apihya.hieloyaventura.com/apihya/ReservaxCodigo?RSV=$request->RSV");   
        return $response->json();
    }

    public function IniciaReserva(Request $request)
    {
        $response = Http::post("https://apihya.hieloyaventura.com/apihya/TUR=$request->TUR&PSJ=$request->PSJ&PRD=$request->PRD&TRF=$request->TRF");   
        return $response->json();
    }

    public function CancelaReserva(Request $request)
    {
        $response = Http::post("https://apihya.hieloyaventura.com/apihya/CancelaReserva?RSV=$request->RSV");   
        return $response->json();
    }
}
