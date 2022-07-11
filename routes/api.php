<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CharacteristicTypeController;
use App\Http\Controllers\ConsultController;
use App\Http\Controllers\ExcurtionController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\LenguageController;
use App\Http\Controllers\UserController;
use App\Models\Lenguage;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
});
Route::controller(UserController::class)->group(function () {
    Route::post('register', 'register');
});
Route::get('faqs', [FaqController::class, 'index']);
Route::prefix('excurtions')->controller(ExcurtionController::class)->group(function () {
    Route::get('/', 'index');
    Route::get('/{id}', 'show');
    Route::get('/by-external-id/{id}', 'showByExternalId');
});

Route::group(['middleware' => ['jwt.verify']], function () {
    Route::post('faqs', [FaqController::class, 'store']);
    Route::post('logout', 'App\Http\Controllers\AuthController@logout');

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
    Route::prefix('characteristics_types')->controller(CharacteristicTypeController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
    });
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
