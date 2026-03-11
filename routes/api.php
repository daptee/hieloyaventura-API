<?php

use App\Http\Controllers\AgencyController;
use App\Http\Controllers\AgencyModuleController;
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
use App\Http\Controllers\PdfCleanupController;
use App\Http\Controllers\PictureExcurtionController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ReservationStatusController;
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
    Route::post('login', 'login')->middleware('throttle:login');
    Route::post('login/admin', 'login_admin')->middleware('throttle:admin-login');
    Route::post('login/agency/user', 'login_agency_user')->middleware('throttle:login');
    Route::post('login/agency/verify-otp', 'verify_agency_otp')->middleware('throttle:login');
});
Route::controller(UserController::class)->group(function () {
    Route::post('register', 'register');
    Route::put('user_edit', 'update')->middleware(['jwt.verify', 'audit.log']);
    Route::put('new_password', 'updatePassword')->middleware(['jwt.verify', 'audit.log']);
});
Route::get('faqs', [FaqController::class, 'index']);
Route::prefix('excurtions')->controller(ExcurtionController::class)->group(function () {
    Route::get('/', 'index');
    Route::get('/{id}', 'show');
    Route::get('/{id}/{language}', 'showByLanguage');
    Route::get('/by-external-id/{id}', 'showByExternalId');
});

Route::post('logout', [AuthController::class, 'logout'])->middleware(['jwt.admin_or_agency', 'audit.log']);

Route::group(['middleware' => ['jwt.verify', 'audit.log']], function () {
    Route::post('faqs', [FaqController::class, 'store']);

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

    Route::get('agency/users/no_admin/{agency_code}', [AgencyUserController::class, 'get_users_no_admin']);
    Route::post('agency/users', [AgencyUserController::class, 'store']);
    Route::post('agency/users/update/{id}', [AgencyUserController::class, 'update']);
    Route::post('agency/users/active_inactive', [AgencyUserController::class, 'active_inactive']);
    Route::post('agency/users/emergency-password-reset', [AgencyUserController::class, 'emergency_password_reset']);

    Route::get('agencies/{agency_code}', [AgencyController::class, 'show']);
    Route::post('agencies', [AgencyController::class, 'store']);
    Route::put('agency/settings', [AgencyController::class, 'updateSettings']);
    Route::post('admin/send-integration-api-welcome', [AgencyController::class, 'sendIntegrationWelcome']);
});

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
    Route::post('/CreaSolicitudAG', 'CreaSolicitudAG');
    Route::get('/SolicitudesAG', 'SolicitudesAG')->middleware(['jwt.agency', 'audit.log']);
    Route::get('/ValidaCupon', 'ValidaCupon');
    Route::get('/CtaCteAG', 'CtaCteAG')->middleware(['jwt.agency', 'audit.log']);
});

Route::prefix('users_reservations')->controller(UserReservationController::class)->group(function () {
    Route::get('/', 'index')->middleware(['jwt.verify', 'audit.log']);
    Route::post('/', 'store');
    Route::get('/{userReservation}', 'show')->middleware(['jwt.verify', 'audit.log']);
    Route::get('/number/{reservationNumber}', 'getByReservationNumber');
    Route::get('/number/encrypted/{reservationNumber}', 'getByReservationNumberEncrypted');
    Route::put('/{id}', 'update');
    Route::get('/get/with_filters', 'get_with_filters')->middleware(['jwt.agency', 'audit.log']);
});


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
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Artisan::call('optimize');

    return response()->json([
        "message" => "Cache cleared successfully"
    ]);
})->middleware(['jwt.verify', 'audit.log']);

// Route::get('test/{trf}/{excursion}', [UserReservationController::class, 'testpdf']);

Route::post('excurtion-characteristics/{id}', [ExcurtionCharacteristicController::class, 'store'])->middleware(['jwt.verify', 'audit.log']);
Route::post('excurtion/characteristics/{id}', [ExcurtionCharacteristicController::class, 'store_excurtion_characteristics'])->middleware(['jwt.verify', 'audit.log']);
Route::post('excurtion/pictures/manage/files', [PictureExcurtionController::class, 'manage'])->middleware(['jwt.verify', 'audit.log']);
Route::get('excurtion/{id}/pictures/files', [PictureExcurtionController::class, 'getByExcurtion']);

Route::post('process-cv', function (Request $request) {
    try {
        $request->validate([
            'nombre_y_apellido' => 'required',
            'email' => 'required',
        ]);

        $cv = $request->file('file');
        $fileName = time() . '.' . $cv->getClientOriginalExtension();

        Storage::putFileAs('public/process-cv', $cv, $fileName);

        $path = "storage/process-cv/$fileName";

        Mail::to("info@hieloyaventura.com")->send(new ProcessCv($request, $path));

        return 'Mail enviado con exito!';
    } catch (\Throwable $th) {
        Log::debug(print_r([$th->getMessage(), $th->getLine()], true));
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
            'email' => 'required',
            'mensaje' => 'required'
        ]);
        Mail::to("info@hieloyaventura.com")->send(new ContactForm($request));
        return 'Mail enviado con exito!';
    } catch (\Throwable $th) {
        Log::debug(print_r([$th->getMessage(), $th->getLine()], true));
        // return $th->getMessage();
        return 'Mail no enviado';
    }
});

Route::post('online-return', function (Request $request) {
    try {
        $request->validate([
            'nro_reserva' => 'required',
            'nombre_y_apellido' => 'required',
            'email' => 'required',
            'telefono' => 'required',
            'mensaje' => 'required'
        ]);

        Mail::to("online@hieloyaventura.com")->send(new OnlineReturn($request));
        return 'Mail enviado con exito!';
    } catch (\Throwable $th) {
        Log::debug(print_r([$th->getMessage(), $th->getLine()], true));
        // return $th->getMessage();
        return 'Mail no enviado';
    }
});

Route::post('group-excurtion', [GroupExcurtionController::class, 'group_excurtion']);

Route::post('recover-password', [UserController::class, 'recover_password_user'])->middleware('throttle:password-recovery');
Route::post('agency-recover-password-user', [UserController::class, 'agency_recover_password_user'])->middleware('throttle:password-recovery');


Route::get('web/general_configuration', [GeneralConfigurationsController::class, 'get_configurations']);
Route::post('web/general_configuration', [GeneralConfigurationsController::class, 'store'])->middleware(['jwt.verify', 'audit.log']);

Route::post('paxs', [PaxController::class, 'store']);
Route::post('agency_paxs', [PaxController::class, 'store_type_agency'])->middleware(['jwt.admin_or_agency', 'audit.log']);


Route::get('modules/user', [UserController::class, 'get_modules']);

Route::resource('reservations', ReservationController::class)->middleware(['jwt.verify', 'audit.log']);
Route::post('reservations/change/assigned_user', [ReservationController::class, 'change_assigned_user'])->middleware(['jwt.verify', 'audit.log']);
Route::post('reservations/resend/email_welcome', [ReservationController::class, 'resend_email_welcome'])->middleware(['jwt.verify', 'audit.log']);
Route::post('reservations/resend/email_voucher', [ReservationController::class, 'resend_email_voucher'])->middleware(['jwt.verify', 'audit.log']);
Route::post('reservations/update/internal_closed/{id}', [ReservationController::class, 'update_internal_closed'])->middleware(['jwt.verify', 'audit.log']);
Route::post('reservations/new/observation', [ReservationController::class, 'new_observation'])->middleware(['jwt.verify', 'audit.log']);

// Agency users
Route::get('agency/users', [AgencyUserController::class, 'index'])->middleware(['jwt.admin_or_agency', 'audit.log']);
Route::get('agency/users/seller/{agency_code}', [AgencyUserController::class, 'get_users_seller'])->middleware(['jwt.admin_or_agency', 'audit.log']);
Route::get('agency/users/types', [AgencyUserController::class, 'types_user_agency'])->middleware(['jwt.admin_or_agency', 'audit.log']);
Route::get('agency/users/filter/code', [AgencyUserController::class, 'filter_code'])->middleware(['jwt.admin_or_agency', 'audit.log']);
Route::get('agency/modules', [AgencyModuleController::class, 'index'])->middleware(['jwt.admin_or_agency', 'audit.log']);
Route::get('agency/reservations/path_file', [UserReservationController::class, 'path_pdf_reservation_agency'])->middleware(['jwt.admin_or_agency', 'audit.log']);

// Agency user reservations
Route::post('agency/users_reservations', [UserReservationController::class, 'store_type_agency'])->middleware(['jwt.admin_or_agency', 'audit.log']);

// Webhook Mercado Libre
Route::post('/mercadopago/notification', [MercadoPagoController::class, 'notificationWebHook']);
// Route::post('/publication/update/price', [MercadoLibreController::class, 'update_publication_price']);
// Route::post('/publication/update/status', [MercadoLibreController::class, 'update_publication_status']);
// Route::post('/publication/update/stock', [MercadoLibreController::class, 'update_publication_stock']);
// Route::post('/publication/upload/invoice', [MercadoLibreController::class, 'upload_publication_invoice']);

// Agency user self-update (uses ID from token, not from URL)
Route::put('/agency/users/profile', [AgencyUserController::class, 'update_self'])->middleware(['jwt.agency', 'audit.log']);
Route::post('/agency/users/profile/confirm-email-change', [AgencyUserController::class, 'confirm_email_change'])->middleware(['jwt.agency', 'audit.log']);
Route::post('/agency/users/profile/confirm-password-change', [AgencyUserController::class, 'confirm_password_change'])->middleware(['jwt.agency', 'audit.log']);
Route::post('/agency/users/seller_load', [AgencyUserController::class, 'user_seller_load'])->middleware(['jwt.admin_or_agency', 'audit.log']);
Route::get('/agency/users/seller_load/{agency_code}', [AgencyUserController::class, 'get_user_seller_load'])->middleware(['jwt.admin_or_agency', 'audit.log']);
Route::post('agency/users/terms_and_conditions', [AgencyUserController::class, 'terms_and_conditions'])->middleware(['jwt.agency', 'audit.log']);
Route::get('/agency/hya/Agencias', [AgencyUserController::class, 'agencies'])->middleware(['jwt.admin_or_agency', 'audit.log']);
Route::get('/agency/hya/Productos', [AgencyUserController::class, 'products']);
Route::get('/agency/hya/TiposPasajeros', [AgencyUserController::class, 'passenger_types']);
Route::get('/agency/hya/Naciones', [AgencyUserController::class, 'nationalities']);
Route::get('/agency/hya/Hoteles', [AgencyUserController::class, 'hotels']);
// Endpoint to receive group files from agencies and send them via email
Route::post('agency/reservations/groups_by', [AgencyUserController::class, 'groups_by'])->middleware(['jwt.agency', 'audit.log']);
Route::get('/agency/hya/Turnos', [AgencyUserController::class, 'shifts']);
Route::post('/agency/hya/IniciaReserva', [AgencyUserController::class, 'start_reservation'])->middleware(['jwt.agency', 'audit.log']);
Route::post('/agency/hya/CancelaReserva', [AgencyUserController::class, 'cancel_reservation'])->middleware(['jwt.agency', 'audit.log']);
Route::post('/agency/hya/ConfirmaReserva', [AgencyUserController::class, 'confirm_reservation'])->middleware(['jwt.agency', 'audit.log']);
Route::post('/agency/hya/ConfirmaPasajeros', [AgencyUserController::class, 'confirm_passengers'])->middleware(['jwt.agency', 'audit.log']);
Route::get('/agency/hya/ReservasAG', [AgencyUserController::class, 'reservationsAG'])->middleware(['jwt.agency', 'audit.log']);
Route::get('/agency/hya/ReservaxCodigo', [AgencyUserController::class, 'ReservaxCodigo'])->middleware(['jwt.agency', 'audit.log']);
Route::post('/agency/users_reservations/request/change', [AgencyUserController::class, 'change_request'])->middleware(['jwt.agency', 'audit.log']);
Route::get('/agency/reservation/{reservation}/requests', [AgencyUserController::class, 'get_reservation_requests'])->middleware(['jwt.agency', 'audit.log']);
Route::get('/agency/hya/ProductosAG', [AgencyUserController::class, 'ProductosAG'])->middleware(['jwt.agency', 'audit.log']);
Route::get('/agency/hya/TurnosAG', [AgencyUserController::class, 'TurnosAG'])->middleware(['jwt.agency', 'audit.log']);
Route::post('/agency/hya/resumen_servicios_diarios', [AgencyUserController::class, 'resumen_servicios_diarios'])->middleware(['jwt.agency', 'audit.log']);
Route::post('/agency/hya/resumen_servicios_diarios/excel', [AgencyUserController::class, 'resumen_servicios_diarios_excel'])->middleware(['jwt.agency', 'audit.log']);

// External Agency HyA routes
Route::prefix('agencies/v1')->middleware('agency.apikey')->controller(App\Http\Controllers\AgencyExternalHyAController::class)->group(function () {
    Route::get('/availability', 'getAvailability');
    Route::get('/hotels', 'getHotels');
    Route::get('/nationalities', 'getNationalities');
    Route::get('/reservations', 'getReservations');
    Route::get('/reservation/{reservation_number}', 'getReservation');
    Route::get('/reservation', function () {
        return response()->json(['message' => 'El número de reserva es obligatorio como parte de la URL (ej: /reservation/123456)'], 400);
    });
    Route::post('/reservation', 'createReservation');
    Route::put('/reservation/{reservation_number}', 'editReservation');
    Route::put('/reservation', function () {
        return response()->json(['message' => 'El número de reserva es obligatorio como parte de la URL (ej: /reservation/123456)'], 400);
    });
    Route::delete('/reservation/{reservation_number}', 'cancelReservation');
    Route::delete('/reservation', function () {
        return response()->json(['message' => 'El número de reserva es obligatorio como parte de la URL (ej: /reservation/123456)'], 400);
    });
    Route::put('/settings', 'updateSettings');
});
// CONSULTAR PARA CARLOS

// TODO - REVISAR QUE LA EDAD SE CALCULE CORRECTAMENTE, SEGUN LA FECHA DE LA EXCURSION

Route::get('/users/types', [UserController::class, 'types_user']);

Route::delete('/pdfs/delete-by-range', [PdfCleanupController::class, 'deleteByRange'])->middleware(['jwt.verify', 'audit.log']);
Route::delete('/pdfs/agencies/delete-by-range', [PdfCleanupController::class, 'deleteByRangeAgencies'])->middleware(['jwt.verify', 'audit.log']);

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
