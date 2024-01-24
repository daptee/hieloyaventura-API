<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use MercadoPago;

class MercadoPagoController extends Controller
{
    public function createPay(Request $request) {

        // SDK de Mercado Pago
        require base_path('vendor/autoload.php');
        // Agrega credenciales
        MercadoPago\SDK::setAccessToken(config('services.mercadopago.dev.token'));

        // Crea un objeto de preferencia
        $preference = new MercadoPago\Preference();
        $preference->back_urls = array(
            "success" => $request->url_back,
            "failure" => $request->url_back,
            "pending" => $request->url_back
        );
        $preference->auto_return = "approved";

        // Crea un ítem en la preferencia
        $item = new MercadoPago\Item();
        $item->title = $request->title;
        $item->quantity = $request->quantity;
        $item->unit_price = $request->unit_price;
        $item->category_descriptor = [
            "route" => [
                "departure_date_time" => $request->departure_date_time
            ]
        ];
        $preference->external_reference = $request->external_reference;
        $preference->items = array($item);
        $preference->payer = [
            "name" => $request->payer_name,
            "email" => $request->payer_email
        ];
        $preference->payment_methods = [
            "excluded_payment_methods" => [
                [
                    "id" => "pagofacil",
                    "id" => "rapipago",
                ]
            ],
            "excluded_payment_types" => [
                [
                    "id" => "ticket"
                ]
            ]
        ];
        $preference->save();

        return response()->json(['preference' => $preference->id], 200);
    }
}
