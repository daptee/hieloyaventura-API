<?php

namespace App\Http\Controllers;

use App\Models\AgencyUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AgencyUserController extends Controller
{
    public $model = AgencyUser::class;
    public $s = "user"; //sustantivo singular
    public $sp = "users"; //sustantivo plural
    public $ss = "user/s"; //sustantivo sigular/plural
    public $v = "o"; //verbo ej:encontrado/a
    public $pr = "el"; //preposicion singular
    public $prp = "los"; //preposicion plural
    
    public function index()
    {
        $users = $this->model::with($this->model::SHOW)->get();

        return response(compact("users"));
    }

    public function store(Request $request)
    {
        $request->validate([
            "agency_user_type_id" => 'required',
            "user" => 'required',
            "password" => 'required',
            "name" => 'required',
            "last_name" => 'required',
            "email" => 'required|unique:agency_users',
            "agency_code" => 'required',
        ]);

        $user = new AgencyUser($request->all());
        $user->password = Hash::make($request->password);
        $user->save();

        $user = AgencyUser::getAllDataUser($user->id);

        return response(compact("user"));
    }

    public function active_inactive(Request $request)
    {
        $request->validate([
            "user_id" => ['required', 'integer', Rule::exists('agency_users', 'id')],
            "active" => ['required', 'in:0,1']
        ]);

        $user = AgencyUser::find($request->user_id);
        $user->active = $request->active;
        $user->save();

        $user = AgencyUser::getAllDataUser($user->id);

        return response(compact("user"));
    }
}
