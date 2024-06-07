<?php

namespace App\Http\Controllers;

use App\Models\AgencyUser;
use App\Models\AgencyUserSellerLoad;
use App\Models\AgencyUserType;
use App\Models\Audit;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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

    public function get_users_seller($agency_code)
    {
        $users = $this->model::with($this->model::SHOW)
                ->where('agency_user_type_id', AgencyUserType::VENDEDOR)
                ->where('agency_code', $agency_code)
                ->get();

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
            "can_view_all_sales" => 'required'
        ]);

        if($request->agency_user_type_id == AgencyUserType::VENDEDOR){
            $agency_user_seller_load = AgencyUserSellerLoad::where('agency_code', $request->agency_code)->first();
            $maximum_load = $agency_user_seller_load->seller_load ?? 5;
            $users_quantity = AgencyUser::where('agency_user_type_id', AgencyUserType::VENDEDOR)->where('agency_code', $request->agency_code)->count();
            if($users_quantity >= $maximum_load){
                return response()->json(["message" => "Cantidad maxima permitida de usuarios vendedores ya completa"], 400);
            }
        }

        $user = new AgencyUser($request->all());
        $user->password = Hash::make($request->password);
        $user->save();

        $user = AgencyUser::getAllDataUser($user->id);

        return response(compact("user"));
    }

    public function update(Request $request, $id)
    {
        // if(!isset(Auth::guard('agency')->user()->agency_code) && !isset(Auth::user()->id))
        //     return response()->json(['message' => 'Token is invalid.'], 400);

        $request->validate([
            // "agency_user_type_id" => 'required',
            // "user" => 'required',
            "name" => 'required',
            "last_name" => 'required',
            "email" => 'required|unique:agency_users,email,' . $id,
            // "agency_code" => 'required',
        ]);

        $user = AgencyUser::find($id);
        // $user->agency_user_type_id = $request->agency_user_type_id;
        // $user->user = $request->user;
        $user->name = $request->name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->can_view_all_sales = $request->can_view_all_sales;

        // $user->agency_code = $request->agency_code;
        
        if($request->password)
            $user->password = Hash::make($request->password);
        
        $user->save();

        $user = AgencyUser::getAllDataUser($user->id);
        $message = "Usuario actualizado con exito";

        return response(compact("user", "message"));
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

    public function types_user_agency(Request $request)
    {
        $types_user = AgencyUserType::all();
        return response(compact("types_user"));
    }

    public function filter_code(Request $request)
    {
        $query = $this->model::with($this->model::SHOW)
                ->when($request->agency_code, function ($query) use ($request) {
                    return $query->where('agency_code', 'LIKE', '%'.$request->agency_code.'%');
                })
                ->orderBy('id', 'desc');
    
        $total = $query->count();
        $total_per_page = 30;
        $data = $query->paginate($total_per_page);
        $current_page = $request->page ?? $data->currentPage();
        $last_page = $data->lastPage();

        $users = $data;

        return response(compact("users", "total", "total_per_page", "current_page", "last_page"));
    }

    public function user_seller_load(Request $request)
    {
        $request->validate([
            'agency_code' => 'required',
        ]);

        $id_user = Auth::guard('agency')->user()->id ?? Auth::user()->id;
        try {
            DB::beginTransaction();
            
            $agency_user_seller_load = AgencyUserSellerLoad::where('agency_code', $request->agency_code)->first();

            if(!isset($agency_user_seller_load)){
                $agency_user_seller_load = new AgencyUserSellerLoad();
            }

            $agency_user_seller_load->agency_code = $request->agency_code;
            $agency_user_seller_load->seller_load = $request->seller_load;
            $agency_user_seller_load->id_user = $id_user;
            $agency_user_seller_load->save();

            Audit::create(["id_user" => $id_user, "action" => ["action" => "maximum load of sellers", "data" => $request->all()]]);
           
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug(["error" => "Error en carga de vendedores (agencia)", "message" => $e->getMessage(), "line" => $e->getLine()]);
            return response()->json(["error" => "Error en carga de vendedores (agencia)", "message" => $e->getMessage(), "line" => $e->getLine()], 500);
        }

        return response()->json(["message" => "Carga de vendedores (agencia) exitosa"], 200);
    }

    public function get_user_seller_load($agency_code)
    {
        $agency_user_seller_load = AgencyUserSellerLoad::with('user')->where('agency_code', $agency_code)->first();
        
        return response()->json(["data" => $agency_user_seller_load], 200);
    }


}
