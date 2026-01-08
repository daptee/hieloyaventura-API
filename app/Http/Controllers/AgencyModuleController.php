<?php

namespace App\Http\Controllers;

use App\Models\AgencyModule;
use Illuminate\Http\Request;

class AgencyModuleController extends Controller
{
    public function index()
    {
        $agency_modules = AgencyModule::all();

        return response(compact("agency_modules"));
    }
}
