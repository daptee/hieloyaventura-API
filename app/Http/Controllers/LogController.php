<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogController extends Controller
{
    public function store_log(Request $request)
    {
        Log::debug("Log-register:", $request->all());

        return response()->json(["message" => "Log guardado correctamente"]);
    }
}
