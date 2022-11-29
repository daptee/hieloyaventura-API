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
        // $preference->back_urls = array(
        //     "success" => "https://prode.soyfutbolero.com/payment/success",
        //     "failure" => "https://prode.soyfutbolero.com/payment/failure",
        //     "pending" => "https://prode.soyfutbolero.com/payment/pending"
        // );
        // $preference->auto_return = "approved";

        // Crea un ítem en la preferencia
        $item = new MercadoPago\Item();
//        $item->id = $request->id;
//        $item->currency_id = $request->currency_id;
//        $item->description = $request->description;
        
        $item->title = $request->title;
        $item->quantity = $request->quantity;
        $item->unit_price = $request->unit_price;
        $preference->items = array($item);
        $preference->payment_methods = array(
            // "excluded_payment_methods" => array(
            //   array("id" => "master")
            // ),
            "excluded_payment_types" => array(
              array("id" => "ticket", "id" => "bank_transfer")
            ),
        );
        $preference->save();

        // dd($preference);
        return response()->json(['preference' => $preference->id], 200);
    }
}
