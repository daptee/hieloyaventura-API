<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class HyAController extends Controller
{    
    public function get_url(){
        $environment = config("app.environment");
        if($environment == "DEV"){
            $url = "https://apihya.hieloyaventura.com/apihya_dev";
        }else{
            $url = "https://apihya.hieloyaventura.com/apihya";
        }
        return $url;
    }
    
    public function passengers_types(Request $request)
    {
        $leng = $request->leng ?? 'ES';
        $url = $this->get_url();
        $response = Http::get("$url/TiposPasajeros?LENG=$leng");   
        return $response->json();
    }

    public function nationalities()
    {
        $url = $this->get_url();
        $response = Http::get("$url/Naciones");   
        return $response->json();
    }

    public function hotels()
    {
        $url = $this->get_url();
        $response = Http::get("$url/Hoteles");   
        return $response->json();
    }
 
    public function rates()
    {
        $url = $this->get_url();
        $response = Http::get("$url/Tarifas");   
        return $response->json();
    }
    
    public function excursions(Request $request)
    {
        $url = $this->get_url();
        $response = Http::get("$url/Productos?FECHA=$request->date");   
        return $response->json();
    }

    public function shifts(Request $request)
    {
        $fecha_desde = $request->date_from;
        $fecha_hasta = $request->date_to;
        $excursion_id = $request->excursion_id;
        $url = $this->get_url();
        $response = Http::get("$url/Turnos?FECHAD=$fecha_desde&FECHAH=$fecha_hasta&PRD=$excursion_id");   
        return $response->json();
    }

    public function ReservaxCodigo(Request $request)
    {
        $url = $this->get_url();
        $response = Http::get("$url/ReservaxCodigo?RSV=$request->RSV");   
        return $response->json();
    }

    public function IniciaReserva(Request $request)
    {
        $url = $this->get_url();
        $response = Http::post("$url/TUR=$request->TUR&PSJ=$request->PSJ&PRD=$request->PRD&TRF=$request->TRF");   
        return $response->json();
    }

    public function CancelaReserva(Request $request)
    {
        $url = $this->get_url();
        $response = Http::post("$url/CancelaReserva?RSV=$request->RSV");   
        return $response->json();
    }
}
