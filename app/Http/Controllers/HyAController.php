<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Spatie\FlareClient\Truncation\TruncationStrategy;

class HyAController extends Controller
{
    public function get_url()
    {
        $environment = config("app.environment");
        if ($environment == "DEV") {
            $url = "https://apihya.hieloyaventura.com/apihya_dev";
        } else {
            $url = "https://apihya.hieloyaventura.com/apihya";
        }
        return $url;
    }

    public function passengers_types(Request $request)
    {
        $leng = $request->leng ?? 'ES';
        $url = $this->get_url();
        $response = Http::get("$url/TiposPasajeros?LENG=$leng");
        if ($response->successful()) {
            return $response->json();
        } else {
            return $response->throw();
        }
    }

    public function nationalities()
    {
        $url = $this->get_url();
        $response = Http::get("$url/Naciones");
        if ($response->successful()) {
            return $response->json();
        } else {
            return $response->throw();
        }
    }

    public function hotels()
    {
        $url = $this->get_url();
        $response = Http::get("$url/Hoteles");
        if ($response->successful()) {
            return $response->json();
        } else {
            return $response->throw();
        }
    }

    public function rates()
    {
        $url = $this->get_url();
        $response = Http::get("$url/Tarifas");
        if ($response->successful()) {
            return $response->json();
        } else {
            return $response->throw();
        }
    }

    public function oferts()
    {
        $url = $this->get_url();
        $response = Http::get("$url/Ofertas");
        if ($response->successful()) {
            return $response->json();
        } else {
            return $response->throw();
        }
    }

    public function excursions(Request $request)
    {
        $url = $this->get_url();
        $response = Http::get("$url/Productos?FECHA=$request->date");
        if ($response->successful()) {
            return $response->json();
        } else {
            return $response->throw();
        }
    }

    public function shifts(Request $request)
    {
        $fecha_desde = $request->date_from;
        $fecha_hasta = $request->date_to;
        $excursion_id = $request->excursion_id;
        $url = $this->get_url();
        $response = Http::get("$url/Turnos?FECHAD=$fecha_desde&FECHAH=$fecha_hasta&PRD=$excursion_id");
        if ($response->successful()) {
            return $response->json();
        } else {
            return $response->throw();
        }
    }

    public function ReservaxCodigo(Request $request)
    {
        $url = $this->get_url();
        $response = Http::get("$url/ReservaxCodigo?RSV=$request->RSV");
        if ($response->successful()) {
            return $response->json();
        } else {
            return $response->throw();
        }
    }

    public function IniciaReserva(Request $request)
    {
        $url = $this->get_url();
        $body_json = $request->all();
        $response = Http::post("$url/IniciaReserva", $body_json);
        if ($response->successful()) {
            return $response->json();
        } else {
            return $response->throw();
        }
    }

    public function CancelaReserva(Request $request)
    {
        $this->validate($request, [
            'RSV' => 'required',
        ]);

        $url = $this->get_url();
        $response = Http::asForm()->post("$url/CancelaReserva", [
            'RSV' => $request->RSV
        ]);
        if ($response->successful()) {
            return $response->json();
        } else {
            return $response->throw();
        }
    }

    public function ConfirmaReserva(Request $request)
    {
        $url = $this->get_url();
        $body_json = $request->all();
        $response = Http::post("$url/ConfirmaReserva", $body_json);
        if ($response->successful()) {
            return $response->json();
        } else {
            return $response->throw();
        }
    }

    public function ConfirmaPasajeros(Request $request)
    {
        $url = $this->get_url();
        $body_json = $request->all();
        $response = Http::post("$url/ConfirmaPasajeros", $body_json);
        if ($response->successful()) {
            return $response->json();
        } else {
            return $response->throw();
        }
    }

    public function Promociones(Request $request)
    {
        $url = $this->get_url();
        $response = Http::get("$url/Promociones?PROD=$request->PROD");
        if ($response->successful()) {
            return $response->json();
        } else {
            return $response->throw();
        }
    }

    public function RecuperaPrecioReserva(Request $request)
    {
        $url = $this->get_url();
        $response = Http::get("$url/RecuperaPrecioReserva?RESERVA=$request->RESERVA");
        if ($response->successful()) {
            return $response->json();
        } else {
            return $response->throw();
        }
    }

    public function CreaSolicitudAG(Request $request)
    {
        $url = $this->get_url();
        $body_json = $request->all();
        $response = Http::post("$url/CreaSolicitudAG", $body_json);
        if ($response->successful()) {
            return $response->json();
        } else {
            return $response->throw();
        }
    }

    public function SolicitudesAG(Request $request)
    {

        $url = $this->get_url();
        $response = Http::get("$url/SolicitudesAG?DESDEF=$request->DESDEF&HASTF=$request->HASTAF&AG=$request->AG");
        if ($response->successful()) {
            return $response->json();
        } else {
            return $response->throw();
        }

        /* // Validaciones manuales para devolver 400
        $requiredFields = ['DESDEF', 'HASTAF', 'AG'];

        foreach ($requiredFields as $field) {
            if (!$request->filled($field)) {
                return response()->json([
                    'message' => 'Faltan campos obligatorios',
                    'error' => "El campo {$field} es obligatorio"
                ], 400);
            }
        }

        $url = $this->get_url();
        $completeURL = $url . "/SolicitudesAG";

        // Armo los parámetros de la query solo con lo que llegue en el request
        $queryParams = http_build_query($request->only([
            'DESDEF',
            'HASTAF',
            'AG',
            'PROD',
            'EST'
        ]));

        $response = Http::get($completeURL . '?' . $queryParams);

        if ($response->successful()) {
            return $response->json();
        } else {
            return $response->throw();
        } */
    }
}
