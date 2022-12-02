<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CharacteristicController;
use App\Http\Controllers\CharacteristicTypeController;
use App\Http\Controllers\ConsultController;
use App\Http\Controllers\ExcurtionCharacteristicController;
use App\Http\Controllers\ExcurtionController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\LenguageController;
use App\Http\Controllers\MedicalRecordController;
use App\Http\Controllers\MercadoPagoController;
use App\Http\Controllers\ReservationStatusController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserReservationController;
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
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
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
    Route::prefix('reservations_status')->controller(ReservationStatusController::class)->group(function () {
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
        Route::post('/', 'store');
        Route::post('/{id}', 'update');
    });
    Route::get('send-email-pdf', [PDFController::class, 'index']);
});

Route::prefix('users_reservations')->controller(UserReservationController::class)->group(function () {
    Route::get('/', 'index')->middleware(['jwt.verify']);
    Route::post('/', 'store');
    Route::get('/{userReservation}', 'show');
    Route::get('/number/{reservationNumber}', 'getByReservationNumber');
    Route::put('/{id}', 'update');
});

Route::post('users_reservations2/',[UserReservationController::class, 'store']);

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
Route::get('/clear-cache', function() {
    Artisan::call('config:clear');
    Artisan::call('optimize');

    return response()->json([
        "message" => "Cache cleared successfully"
    ]);
});

Route::get('/storage-link', function(){
    Artisan::call('storage:link');

    return response()->json([
        "message" => "The links have been created."
    ]);
});
// Route::get('test/{trf}/{excursion}', [UserReservationController::class, 'testpdf']);

Route::get('test-mail', function() {
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

Route::post('process-cv', function(Request $request) {
    try {
        $request->validate([
            'nombre_y_apellido' => 'required',
            'email' => 'required',
        ]);

        $cv = $request->file('file');
        $fileName   = time() . '.' . $cv->getClientOriginalExtension();
        
        Storage::putFileAs('public/process-cv', $cv, $fileName);

        $path = "storage/process-cv/$fileName";

        Mail::to("enzo100amarilla@gmail.com")->send(new ProcessCv($request, $path));

        return 'Mail enviado con exito!';
    } catch (\Throwable $th) {
        Log::debug(print_r([$th->getMessage(), $th->getLine()],  true));
        // return $th->getMessage();
        return 'Mail no enviado';
    }
});

Route::post('payment/mercadopago/preference', [MercadoPagoController::class, 'createPay']);

Route::get('diseases/{language_id}', [MedicalRecordController::class, 'diseases']);

Route::post('passenger/diseases', [MedicalRecordController::class, 'passenger_diseases']);