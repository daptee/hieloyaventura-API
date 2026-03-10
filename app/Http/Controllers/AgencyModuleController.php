<?php

namespace App\Http\Controllers;

use App\Models\AgencyModule;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgencyModuleController extends Controller
{
    public function index()
    {
        if (!Auth::guard('agency')->check()) {
            if ($error = $this->requireAdminModule(Module::AGENCIAS)) return $error;
        }

        $agency_modules = AgencyModule::all();

        return response(compact("agency_modules"));
    }
}
