<?php

namespace App\Http\Controllers;

use App\Mail\MercadoPagoNotification;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use MercadoPago;
use stdClass;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\Config;

class MercadoPagoController extends Controller
{
    // public function createPay(Request $request) {

    //         // SDK de Mercado Pago
    //         require base_path('vendor/autoload.php');
    //         // Agrega credenciales
    //         Log::debug(config('services.mercadopago.dev.token'));
    //         MercadoPago\SDK::setAccessToken(config('services.mercadopago.dev.token'));

    //         // Crea un objeto de preferencia
    //         $preference = new MercadoPago\Preference();
    //         $preference->back_urls = array(
    //             "success" => $request->url_back,
    //             "failure" => $request->url_back,
    //             "pending" => $request->url_back
    //         );
    //         $preference->auto_return = "approved";

    //         // Crea un ítem en la preferencia
    //         $item = new MercadoPago\Item();
    //         $item->title = $request->title;
    //         $item->quantity = $request->quantity;
    //         $item->unit_price = $request->unit_price;
    //         // $item->departure_date_time = $request->departure_date_time;

    //         $category_descriptor = [
    //             "route" => [
    //                 "departure_date_time" => $request->departure_date_time
    //             ]
    //         ];
    //         $item->category_descriptor = $category_descriptor;
    //         // $item->category_descriptor->route->departure_date_time = $request->category_descriptor;
    //         // $item->category_id = "departure_" . strtotime($request->departure_date_time); 
    //         // $item->category_id = strtotime($request->departure_date_time); 
    //         // $item->category_id = $request->departure_date_time; 
    //         $preference->items = array($item);

    //         $object_payer = new stdClass;
    //         $object_payer->name = $request->payer_name;
    //         $object_payer->email = $request->payer_email;
    //         $preference->payer = $object_payer;
    //         $preference->external_reference = $request->external_reference;
    //         $preference->payment_methods = [
    //             "excluded_payment_methods" => [
    //                 [
    //                     "id" => "pagofacil"
    //                 ],
    //                 [
    //                     "id" => "rapipago"
    //                 ]
    //             ],
    //             "excluded_payment_types" => [
    //                 [
    //                     "id" => "ticket"
    //                 ]
    //             ]
    //         ];
    //         $preference->notification_url = config('app.url') . '/api/mercadopago/notification';
    //         $preference->save();

    //         // return response()->json(['preference' => $preference->id], 200);
    //         return response()->json(['preference' => $preference->items], 200);
    // }

    public function createPay(Request $request)
    {
        require base_path('vendor/autoload.php');
        Log::debug(config('services.mercadopago.dev.token'));
        MercadoPago\SDK::setAccessToken(config('services.mercadopago.dev.token'));

        $preference = new MercadoPago\Preference();
        $preference->back_urls = array(
            "success" => $request->url_back,
            "failure" => $request->url_back,
            "pending" => $request->url_back
        );
        $preference->auto_return = "approved";

        $items = [
            [ 
                "title" => $request->title,
                "quantity" => (int)$request->quantity,
                "unit_price" => (int)$request->unit_price,
                "category_descriptor" => [ 
                    "route" => [ 
                        "departure_date_time" => "2024-04-12T12:58:41.425-04:00"
                    ]
                ] 
            ] 
        ]; 
        $preference->items = $items;

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
        $preference->notification_url = config('app.url') . '/api/mercadopago/notification';
        $preference->save();

        return response()->json(['preference' => $preference->id], 200);
}

    // public function createPay(Request $request) 
    // {
    //     // SDK de Mercado Pago
    //     require base_path('vendor/autoload.php');
    //     // Agrega credenciales
    //     Log::debug(config('services.mercadopago.dev.token'));

    //     MercadoPago\SDK::setAccessToken(config('services.mercadopago.dev.token'));
    //     $preference = new MercadoPago\Preference();
    //     $items = [
    //         [ 
    //             "title" => $request->title,
    //             "quantity" => (int)$request->quantity,
    //             "unit_price" => (int)$request->unit_price,
    //             "category_descriptor" => [ 
    //                 "route" => [ 
    //                     "departure_date_time" => $request->departure_date_time
    //                 ]
    //             ] 
    //         ] 
    //     ]; 
    //     $preference->items = $items;
    //     $preference->save(); 

    //     return response()->json(['preference' => $preference], 200);
    // }

    public function notificationWebHook(Request $request)
    {
        Log::channel("notificationmp")->info("request data: " . json_encode($request->all()));
        $payment = null;
        $id = null;
        $payment_status = null;
        $external_reference = null;
        try {
            $token = config('services.mercadopago.dev.token');

            if($request->type == "payment"){
                MercadoPago\SDK::setAccessToken($token);

                if(isset($request['data']['id'])){
                    $id = $request['data']['id']; 
                }else if(isset($request->data->id)){
                    $id = $request->data->id;
                }

                // if(isset($id)){
                //     $response_payment = Http::withHeaders([
                //         'Authorization' => 'Bearer '.$token,
                //         'Content-Type' => 'application/json',
                //     ])->get("https://api.mercadopago.com/v1/payments/$id");


                //     if($response_payment->status() != 200){
                //         Log::channel("notificationmp")->info("error GET Payment: $id");
                //         Log::channel("notificationmp")->info("message error: " . json_encode($response_payment->json()));
                //     }else{
                //         $payment = $response_payment->json();
        
                //         if(isset($payment['status'])){
                //             $payment_status = $payment['status'];
                //         }else if(isset($payment->status)){
                //             $payment_status = $payment->status;
                //         }

                //         if(isset($payment['external_reference'])){
                //             $external_reference = $payment['external_reference'];
                //         }else if(isset($payment->external_reference)){
                //             $external_reference = $payment->external_reference;
                //         }

                //         if(isset($payment_status) && isset($external_reference)){
            
                //             if($payment_status == "cancelled" || $payment_status == "rejected" || $payment_status == "refunded" || $payment_status == "charged_back"){
                //                 try {
                //                     Mail::to("ecommerce@hieloyaventura.com")->send(new MercadoPagoNotification($payment_status, $id, $external_reference));                        
                //                 } catch (Exception $e) {
                //                     Log::channel("notificationmperror")->info("error: " . $e->getMessage() . ", error en envio de mail a ventas@hieloyaventura.com, numero de pago: $id" . ", line: " . $e->getLine());
                //                 }
                //             }

                //         }else{
                //             Log::channel("notificationmperror")->info("error: error en envio de mail a ventas@hieloyaventura.com, no se encontro payment status o external reference. Numero de pago: $id");
                //         }
        
                //         Log::channel("notificationmp")->info("response payment json: " . json_encode($response_payment->json()));
                //     }
                // }else{
                //     Log::channel("notificationmperror")->info('error: ID no encontrado');
                // }
            }
        } catch (Exception $e) {
            Log::channel("notificationmperror")->info('error: ' . $e->getMessage() . ', line: ' . $e->getLine());
        }
        
        Log::channel("notificationmp")->info("<------------------ ------------------>");

        return response()->json(["payment" => $payment], 200);
    }

}
