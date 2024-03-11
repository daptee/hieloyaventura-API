<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use MercadoPago;
use stdClass;
use Illuminate\Support\Facades\Http;

class MercadoPagoController extends Controller
{
    public function createPay(Request $request) {

        // SDK de Mercado Pago
        require base_path('vendor/autoload.php');
        // Agrega credenciales
        Log::debug(config('services.mercadopago.dev.token'));
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
        // $category_descriptor = new stdClass;
        // $category_descriptor_route = new stdClass;
        // $category_descriptor_route->departure_date_time = $request->departure_date_time;
        // $category_descriptor->route = $category_descriptor_route; 

        // $category_descriptor = [
        //     "route" => [
        //         "departure_date_time" => $request->departure_date_time
        //     ]
        // ];
        // $item->category_descriptor = array(
            // "route" => array(
                // "departure_date_time" => $request->departure_date_time
            // )
        // );
        $item->departure_date_time = $request->departure_date_time;
        $preference->items = array($item);

        $object_payer = new stdClass;
        $object_payer->name = $request->payer_name;
        $object_payer->email = $request->payer_email;
        $preference->payer = $object_payer;
        $preference->external_reference = $request->external_reference;
        $preference->payment_methods = [
            "excluded_payment_methods" => [
                [
                    "id" => "pagofacil"
                ],
                [
                    "id" => "rapipago"
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

    public function notificationWebHook(Request $request)
    {
        Log::channel("notificationmp")->info($request);
        $payment = null;
        try {
            $token = config('services.mercadopago.webhook.token');
            MercadoPago\SDK::setAccessToken($token);
            $data = $request;
            $type = $data->type ?? null;
            Log::channel("notificationmp")->info("type: $type");
            $id = $data->data['id'];
            Log::channel("notificationmp")->info($id);
            $payment = MercadoPago\Payment::find_by_id($id);
            Log::channel("notificationmp")->info($payment);

            $payment2 = Http::withHeaders([
                'Authorization' => 'Bearer '.$token,
                'Content-Type' => 'application/json',
            ])->get("https://api.mercadopago.com/v1/payments/$id");

            Log::channel("notificationmp")->info($payment2);
        } catch (Exception $e) {
            Log::channel("notificationmperror")->info('error: ' . $e->getMessage() . ', line: ' . $e->getLine());
        }
        
        return response()->json(["payment" => $payment], 200);
    }

}
