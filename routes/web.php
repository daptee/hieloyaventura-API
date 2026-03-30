<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Documentation - Protected (requires API authentication)
// The OpenAPI specification is served from API at /api/docs/openapi.json (JWT required)
Route::get('/docs', function () {
    // Si quieres requerir autenticación aquí también, descomentar las líneas abajo:
    // if (!auth('authTokenForWeb')->check()) {
    //     return response()->json(['message' => 'Unauthorized. Please authenticate first.'], 401);
    // }
    return response()->file(public_path('docs/index.html'));
});
