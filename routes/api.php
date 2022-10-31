<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CharacteristicController;
use App\Http\Controllers\CharacteristicTypeController;
use App\Http\Controllers\ConsultController;
use App\Http\Controllers\ExcurtionController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\LenguageController;
use App\Http\Controllers\ReservationStatusController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserReservationController;
use App\Models\Lenguage;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

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
    // Route::post('/', 'store');
    Route::get('/{userReservation}', 'show');
    Route::put('/{id}', 'update');
});

Route::post('users_reservations/',[UserReservationController::class, 'store']);

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
    Artisan::call('optimize');

    return response()->json([
        "message" => "Cache cleared successfully"
    ]);
});

// Route::get('test', [UserReservationController::class, 'testpdf']);
