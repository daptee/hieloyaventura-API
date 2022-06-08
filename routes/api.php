<?php

use App\Http\Controllers\CharacteristicTypeController;
use App\Http\Controllers\ConsultController;
use App\Http\Controllers\ExcurtionController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\LenguageController;
use App\Http\Controllers\UserController;
use App\Models\Lenguage;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

Route::controller(UserController::class)->group(function () {
    Route::post('register', 'register');
    Route::post('login', 'authenticate');
});

Route::group(['middleware' => ['jwt.verify']], function () {
    Route::resource('faqs', FaqController::class)->only([
        'index', 'store',
    ]);
    Route::post('logout', 'App\Http\Controllers\AuthController@logout');

    Route::prefix('consults')->controller(ConsultController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/change', 'changeConsultEmail');
    });
    Route::prefix('lenguages')->controller(LenguageController::class)->group(function () {
        Route::get('/', 'index');
    });
    Route::prefix('excurtions')->controller(ExcurtionController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/{id}', 'show');
        Route::post('/', 'store');
    });
    Route::prefix('characteristics_types')->controller(CharacteristicTypeController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
    });
});


Route::get('/lenguage/{locale}', function ($locale) {
    if (!in_array($locale, Lenguage::get()->pluck('id')->toArray())) {
        abort(400);
    }
    App::setLocale($locale);
});

Route::prefix('consults')->controller(ConsultController::class)->group(function () {
    Route::post('/', 'store');
});
