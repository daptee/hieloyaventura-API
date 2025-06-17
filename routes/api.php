<?php

use App\Http\Controllers\AgencyUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CharacteristicController;
use App\Http\Controllers\CharacteristicTypeController;
use App\Http\Controllers\ConsultController;
use App\Http\Controllers\ExcurtionCharacteristicController;
use App\Http\Controllers\ExcurtionController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\GeneralConfigurationsController;
use App\Http\Controllers\GroupExcurtionController;
use App\Http\Controllers\HyAController;
use App\Http\Controllers\LenguageController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\MedicalRecordController;
use App\Http\Controllers\MercadoLibreController;
use App\Http\Controllers\MercadoPagoController;
use App\Http\Controllers\PaxController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ReservationStatusController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserReservationController;
use App\Mail\ContactForm;
use App\Mail\OnlineReturn;
use App\Mail\ProcessCv;
use App\Mail\TestMail;
use App\Models\Lenguage;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Client;

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('login/admin', 'login_admin');
    Route::post('login/agency/user', 'login_agency_user');
});
Route::controller(UserController::class)->group(function () {
    Route::post('register', 'register');
    Route::put('user_edit', 'update')->middleware(['jwt.verify']);
    Route::put('new_password', 'updatePassword')->middleware(['jwt.verify']);
});
Route::get('faqs', [FaqController::class, 'index']);
Route::prefix('excurtions')->controller(ExcurtionController::class)->group(function () {
    Route::get('/', 'index');
    Route::get('/{id}', 'show');
    Route::get('/{id}/{language}', 'showByLanguage');
    Route::get('/by-external-id/{id}', 'showByExternalId');
});

Route::group(['middleware' => ['jwt.verify']], function () {
    Route::post('faqs', [FaqController::class, 'store']);
    Route::post('logout', [AuthController::class, 'logout']);

    Route::prefix('consults')->controller(ConsultController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/change', 'changeConsultEmail');
    });
    Route::prefix('lenguages')->controller(LenguageController::class)->group(function () {
        Route::get('/', 'index');
    });
    Route::prefix('excurtions')->controller(ExcurtionController::class)->group(function () {
        Route::post('/', 'store');
        Route::post('/{id}', 'update');
    });
    Route::prefix('characteristics')->controller(CharacteristicController::class)->group(function () {
        Route::post('/', 'store');
        Route::post('/array', 'storeArray');
        Route::post('/{id}/excurtion', 'arrayAddToExcurtion');
        Route::get('/{id}', 'show');
        Route::put('/{id}', 'update');
        Route::put('/{id}/array', 'updateArray');
    });
    Route::prefix('icons')->controller(ExcurtionController::class)->group(function () {
        Route::post('/', 'store');
        Route::post('/{id}', 'update');
    });
    Route::prefix('characteristics_types')->controller(CharacteristicTypeController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
    });
    Route::prefix('nationalities')->controller(LenguageController::class)->group(function () {
        Route::get('/', 'index');
    });
    Route::prefix('users')->controller(UserController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/get_all/with_out_filters', 'get_all_with_out_filters');
        Route::post('/', 'store');
        // Route::post('/create/admin', 'store_admin');
        Route::post('/{id}', 'update');
        Route::delete('/{id}', 'destroy');
        Route::put('/{id}/admin', 'update_admin');
    });
    // Route::get('send-email-pdf', [PDFController::class, 'index']);

    Route::get('agency/users/seller/{agency_code}', [AgencyUserController::class, 'get_users_seller']);
    Route::post('agency/users', [AgencyUserController::class, 'store']);
    Route::post('agency/users/update/{id}', [AgencyUserController::class, 'update']);
    Route::post('agency/users/active_inactive', [AgencyUserController::class, 'active_inactive']);

    Route::post('tickets', [TicketController::class, 'store']);
    Route::post('tickets/message', [TicketController::class, 'message']);
    Route::post('tickets/change/status', [TicketController::class, 'change_status']);
});

Route::get('tickets', [TicketController::class, 'index']);

Route::post('create/log', [LogController::class, 'store_log']);

Route::prefix('reservations_status')->controller(ReservationStatusController::class)->group(function () {
    Route::get('/', 'index');
});

Route::prefix('hya')->controller(HyAController::class)->group(function () {
    Route::get('/passengers_types', 'passengers_types');
    Route::get('/nationalities', 'nationalities');
    Route::get('/hotels', 'hotels');
    Route::get('/rates', 'rates');
    Route::get('/oferts', 'oferts');
    Route::get('/excursions', 'excursions');
    Route::get('/shifts', 'shifts');
    Route::get('/ReservaxCodigo', 'ReservaxCodigo');
    Route::post('/IniciaReserva', 'IniciaReserva');
    Route::post('/CancelaReserva', 'CancelaReserva');
    Route::post('/ConfirmaReserva', 'ConfirmaReserva');
    Route::post('/ConfirmaPasajeros', 'ConfirmaPasajeros');
    Route::get('/Promociones', 'Promociones');
    Route::get('/RecuperaPrecioReserva', 'RecuperaPrecioReserva');
});

Route::prefix('users_reservations')->controller(UserReservationController::class)->group(function () {
    Route::get('/', 'index')->middleware(['jwt.verify']);
    Route::post('/', 'store');
    Route::get('/{userReservation}', 'show');
    Route::get('/number/{reservationNumber}', 'getByReservationNumber');
    Route::get('/number/encrypted/{reservationNumber}', 'getByReservationNumberEncrypted');
    Route::put('/{id}', 'update');
    Route::get('/get/with_filters', 'get_with_filters')->middleware(['jwt.verify']);
});

Route::post('users_reservations2/', [UserReservationController::class, 'store']);

Route::get('/lenguages/{locale}', function ($locale) {
    //1 => spanish
    //2 => english
    //3 => portugueis
    if (!in_array($locale, Lenguage::get()->pluck('id')->toArray())) {
        abort(400);
    }
    if (Auth::check()) {
        User::whereId(Auth::id())->update('lenguage_id', $locale);
    }
    Session::put('applocale', $locale);
    // App::setLocale($locale);
    // dd(App::getLocale());

});

Route::prefix('consults')->controller(ConsultController::class)->group(function () {
    Route::post('/', 'store');
});
Route::get('/clear-cache', function () {
    Artisan::call('config:clear');
    Artisan::call('optimize');

    return response()->json([
        "message" => "Cache cleared successfully"
    ]);
});

Route::get('/clear-tokens', function () {
    Artisan::call('passport:purge');
    Artisan::call('passport:install');

    return response()->json([
        "message" => "Tokens config successfully"
    ]);
});

Route::get('/storage-link', function () {
    Artisan::call('storage:link');

    return response()->json([
        "message" => "The links have been created."
    ]);
});
// Route::get('test/{trf}/{excursion}', [UserReservationController::class, 'testpdf']);

Route::get('test-mail', function () {
    try {
        $text = "Test de envio de mail Hielo y Aventura";
        Mail::to("enzo100amarilla@gmail.com")->send(new TestMail("enzo100amarilla@gmail.com", $text));
        return 'Mail enviado';
    } catch (\Throwable $th) {
        Log::debug(print_r([$th->getMessage(), $th->getLine()],  true));
        return 'Mail no enviado';
    }
});

Route::post('testeando-curl-post', function () {
    try {
        $text = "Test de envio de mail Hielo y Aventura";
        Mail::to("enzo100amarilla@gmail.com")->send(new TestMail("enzo100amarilla@gmail.com", $text));
        return 'Mail enviado';
    } catch (\Throwable $th) {
        Log::debug(print_r([$th->getMessage(), $th->getLine()],  true));
        return 'Mail no enviado';
    }
});

Route::post('excurtion-characteristics/{id}', [ExcurtionCharacteristicController::class, 'store']);
Route::post('excurtion/characteristics/{id}', [ExcurtionCharacteristicController::class, 'store_excurtion_characteristics']);

Route::post('process-cv', function (Request $request) {
    try {
        $request->validate([
            'nombre_y_apellido' => 'required',
            'email' => 'required',
        ]);

        $cv = $request->file('file');
        $fileName   = time() . '.' . $cv->getClientOriginalExtension();

        Storage::putFileAs('public/process-cv', $cv, $fileName);

        $path = "storage/process-cv/$fileName";

        Mail::to("info@hieloyaventura.com")->send(new ProcessCv($request, $path));

        return 'Mail enviado con exito!';
    } catch (\Throwable $th) {
        Log::debug(print_r([$th->getMessage(), $th->getLine()],  true));
        // return $th->getMessage();
        return 'Mail no enviado';
    }
});

Route::post('payment/mercadopago/preference', [MercadoPagoController::class, 'createPay']);

Route::get('diseases/{language_id}', [MedicalRecordController::class, 'diseases']);

Route::post('passenger/diseases/{hash_reservation_number}/{mail_to}', [MedicalRecordController::class, 'passenger_diseases']);

Route::post('medical/record', [MedicalRecordController::class, 'medical_record']);

// Route::post('contact', [ContactController::class, 'form_contact']);
Route::post('contact-form', function (Request $request) {
    try {
        $request->validate([
            'nombre_y_apellido' => 'required',
            'email'             => 'required',
            'mensaje'           => 'required'
        ]);
        Mail::to("info@hieloyaventura.com")->send(new ContactForm($request));
        return 'Mail enviado con exito!';
    } catch (\Throwable $th) {
        Log::debug(print_r([$th->getMessage(), $th->getLine()],  true));
        // return $th->getMessage();
        return 'Mail no enviado';
    }
});

Route::post('online-return', function (Request $request) {
    try {
        $request->validate([
            'nro_reserva'       => 'required',
            'nombre_y_apellido' => 'required',
            'email'             => 'required',
            'telefono'          => 'required',
            'mensaje'           => 'required'
        ]);

        Mail::to("online@hieloyaventura.com")->send(new OnlineReturn($request));
        return 'Mail enviado con exito!';
    } catch (\Throwable $th) {
        Log::debug(print_r([$th->getMessage(), $th->getLine()],  true));
        // return $th->getMessage();
        return 'Mail no enviado';
    }
});

Route::post('group-excurtion', [GroupExcurtionController::class, 'group_excurtion']);

Route::post('recover-password', [UserController::class, 'recover_password_user']);
Route::post('agency-recover-password-user', [UserController::class, 'agency_recover_password_user']);


Route::get('web/general_configuration', [GeneralConfigurationsController::class, 'get_configurations']);
Route::post('web/general_configuration', [GeneralConfigurationsController::class, 'store'])->middleware(['jwt.verify']);

Route::post('paxs', [PaxController::class, 'store']);
Route::post('agency_paxs', [PaxController::class, 'store_type_agency'])->middleware(['jwt.verify']);

Route::get('test-cancelar-reserva', [UserReservationController::class, 'test_cancelar_reserva']);

Route::get('test-api-cr', function () {

    $client = new Client();

    $fields = json_encode(array("RSV" => "349268"));
    $url = config('app.api_hya') . "/CancelaReservaM2";
    $response = $client->post($url, [
        'body' => $fields,
        'headers' => [
            'Content-Type' => 'application/json'
        ]
    ]);

    $body = $response->getBody();

    return $body;
});

Route::get('curl/test-api-cancelar/reserva', function () {

    try {
        $url = config('app.api_hya') . "/CancelaReservaM2";

        $curl = curl_init();
        $fields = json_encode(array("RSV" => "432837"));
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $resp = curl_exec($curl);
        curl_close($curl);

        return "entre en try, resp: $resp";
    } catch (\Throwable $th) {
        Log::debug(print_r([$th->getMessage(), $th->getLine()],  true));
        // return $th->getMessage();
        return "entre en catch";
    }

    // $url = config('app.api_hya')."/CancelaReservaM2";

    // $curl = curl_init();
    // $fields = json_encode( array("RSV" => "432837") );
    // curl_setopt($curl, CURLOPT_URL, $url);
    // curl_setopt($curl, CURLOPT_POST, true);
    // curl_setopt($curl, CURLOPT_POSTFIELDS, $fields);
    // curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
    // curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    // $resp = curl_exec($curl);
    // curl_close($curl);

    // return $resp;
});

Route::get('modules/user', [UserController::class, 'get_modules']);

Route::resource('reservations', ReservationController::class)->middleware(['jwt.verify']);
Route::post('reservations/change/assigned_user', [ReservationController::class, 'change_assigned_user'])->middleware(['jwt.verify']);
Route::post('reservations/resend/email_welcome', [ReservationController::class, 'resend_email_welcome'])->middleware(['jwt.verify']);
Route::post('reservations/resend/email_voucher', [ReservationController::class, 'resend_email_voucher'])->middleware(['jwt.verify']);
Route::post('reservations/update/internal_closed/{id}', [ReservationController::class, 'update_internal_closed'])->middleware(['jwt.verify']);
Route::post('reservations/new/observation', [ReservationController::class, 'new_observation'])->middleware(['jwt.verify']);

// Agency users 
Route::get('agency/users', [AgencyUserController::class, 'index']);
Route::get('agency/users/types', [AgencyUserController::class, 'types_user_agency']);
Route::get('agency/users/filter/code', [AgencyUserController::class, 'filter_code']);
Route::get('agency/reservations/path_file', [UserReservationController::class, 'path_pdf_reservation_agency'])->middleware(['jwt.verify']);

// Agency user reservations
Route::post('agency/users_reservations', [UserReservationController::class, 'store_type_agency'])->middleware(['jwt.verify']);

// Webhook Mercado Libre
Route::post('/mercadopago/notification', [MercadoPagoController::class, 'notificationWebHook']);
// Route::post('/publication/update/price', [MercadoLibreController::class, 'update_publication_price']);
// Route::post('/publication/update/status', [MercadoLibreController::class, 'update_publication_status']);
// Route::post('/publication/update/stock', [MercadoLibreController::class, 'update_publication_stock']);
// Route::post('/publication/upload/invoice', [MercadoLibreController::class, 'upload_publication_invoice']);

Route::post('/agency/users/seller_load', [AgencyUserController::class, 'user_seller_load'])->middleware(['jwt.verify']);
Route::get('/agency/users/seller_load/{agency_code}', [AgencyUserController::class, 'get_user_seller_load'])->middleware(['jwt.verify']);
Route::post('agency/users/terms_and_conditions', [AgencyUserController::class, 'terms_and_conditions'])->middleware(['jwt.verify']);
Route::get('/agency/hya/Agencias', [AgencyUserController::class, 'agencies']);
Route::get('/agency/hya/Productos', [AgencyUserController::class, 'products']);
Route::get('/agency/hya/TiposPasajeros', [AgencyUserController::class, 'passenger_types']);
Route::get('/agency/hya/Naciones', [AgencyUserController::class, 'nationalities']);
Route::get('/agency/hya/Hoteles', [AgencyUserController::class, 'hotels']);
Route::get('/agency/hya/Turnos', [AgencyUserController::class, 'shifts']);
Route::post('/agency/hya/IniciaReserva', [AgencyUserController::class, 'start_reservation']);
Route::post('/agency/hya/CancelaReserva', [AgencyUserController::class, 'cancel_reservation']);
Route::post('/agency/hya/ConfirmaReserva', [AgencyUserController::class, 'confirm_reservation']);
Route::post('/agency/hya/ConfirmaPasajeros', [AgencyUserController::class, 'confirm_passengers']);
Route::get('/agency/hya/ReservasAG', [AgencyUserController::class, 'reservationsAG']);
Route::get('/agency/hya/ReservaxCodigo', [AgencyUserController::class, 'ReservaxCodigo']);
Route::post('/agency/users_reservations/request/change', [AgencyUserController::class, 'change_request']);
Route::get('/agency/hya/ProductosAG', [AgencyUserController::class, 'ProductosAG']);
Route::get('/agency/hya/TurnosAG', [AgencyUserController::class, 'TurnosAG']);

// Route::get('test-notification-user', function(){
//     $r_10_min_data = [
//         'email' => 'enzoamarilla@gmail.com',
//         'subject' => "Hielo & Aventura - aviso carga de pasajeros - nro de reserva 12345",
//         'msg' => "Hola. Enviamos este correo para notificarle que su compra de la excursion nro 12345, no esta confirmada. Para ello, debe terminar de completar los datos de los pasajeros de la misma. Puede realizarlo desde el siguiente link:

//                 IMPORTANTEe: Recuerde que si no completa estos datos, su reserva puede ser cancelada.
                  
//                 Muchas gracias. El equipo de Hielo & Aventura."
//     ];
//     return new NotificacionPasajero($r_10_min_data);
// });
