<?php

namespace App\Http\Controllers;

use App\Mail\ReservationRequestChange;
use App\Mail\ReservationRequestChange2;
use App\Models\AgencyUser;
use App\Models\AgencyUserSellerLoad;
use App\Models\AgencyUserType;
use App\Models\Audit;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Tymon\JWTAuth\Http\Parser\AuthHeaders;

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
        $user->agency_user_type_id = $request->agency_user_type_id;
        $user->user = $request->user;
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

    public function terms_and_conditions()
    {
        if(Auth::guard('agency')->user()->agency_user_type_id == AgencyUserType::ADMIN){
            $id = Auth::guard('agency')->user()->id;
        }else{
            return response()->json(['message' => 'Usuario no válido.'], 400);
        }
        
        $user = AgencyUser::find($id);
        
        try {
            DB::beginTransaction();
            //code...
            $user->terms_and_conditions = now()->format('Y-m-d H:i:s');
            $user->save();

            Audit::create(["id_user" => $id, "action" => ["action" => "Acepta terminos y condiciones.", "data" => null]]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug(["error" => "Error en carga de terminos y condiciones (usuario agencia)", "message" => $e->getMessage(), "line" => $e->getLine()]);
            return response()->json(["error" => "Error en carga de terminos y condiciones (usuario agencia)", "message" => $e->getMessage(), "line" => $e->getLine()], 500);
        }

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

    // HYA ENDPOINTS

    public function get_url(){
        $environment = config("app.environment");
        if($environment == "DEV"){
            $url = "https://apihya.hieloyaventura.com/apihya_dev";
        }else{
            $url = "https://apihya.hieloyaventura.com/apihya";
        }
        return $url;
    }

    public function agencies(Request $request)
    {
        $params = [];

        if ($request->has('DESDE') && $request->DESDE !== null) {
            $params['DESDE'] = $request->DESDE;
        }
        if ($request->has('HASTA') && $request->HASTA !== null) {
            $params['HASTA'] = $request->HASTA;
        }

        $url = $this->get_url();
        $query = http_build_query($params);
        $response = Http::get("$url/Agencias?$query");
        
        if ($response->successful()) {
            return $response->json();
        } else {
            return $response->throw();
        }
    }

    public function products(Request $request)
    {
        $fecha = $request->FECHA;
        $url = $this->get_url();
        $response = Http::get("$url/Productos?FECHA=$fecha");   
        if ($response->successful()) {
            return $response->json();
        } else {
            return $response->throw();
        }
    }

    public function passenger_types(Request $request)
    {
        $leng = $request->LENG ?? 'ES';
        $url = $this->get_url();
        $response = Http::get("$url/TiposPasajeros?LENG=$leng");   
        if ($response->successful()) {
            return $response->json();
        } else {
            return $response->throw();
        }
    }

    public function nationalities()
    {
        $url = $this->get_url();
        $response = Http::get("$url/Naciones");   
        if ($response->successful()) {
            return $response->json();
        } else {
            return $response->throw();
        }
    }

    public function hotels()
    {
        $url = $this->get_url();
        $response = Http::get("$url/Hoteles");   
        if ($response->successful()) {
            return $response->json();
        } else {
            return $response->throw();
        }
    }

    public function shifts(Request $request)
    {
        $fecha_desde = $request->FECHAD;
        $fecha_hasta = $request->FECHAH;
        $excursion_id = $request->PRD;
        $url = $this->get_url();
        $response = Http::get("$url/Turnos?FECHAD=$fecha_desde&FECHAH=$fecha_hasta&PRD=$excursion_id");   
        if ($response->successful()) {
            return $response->json();
        } else {
            return $response->throw();
        }
    }

    public function start_reservation(Request $request)
    {
        $url = $this->get_url();
        $body_json = $request->all();
        $response = Http::post("$url/IniciaReserva", $body_json);   
        if ($response->successful()) {
            return $response->json();
        } else {
            return $response->throw();
        }
    }

    public function cancel_reservation(Request $request)
    {
        $this->validate($request, [
            'RSV' => 'required',
        ]);
        
        $url = $this->get_url();
        $response = Http::asForm()->post("$url/CancelaReserva", [
            'RSV' => $request->RSV   
        ]);
        if ($response->successful()) {
            return $response->json();
        } else {
            return $response->throw();
        }
    }

    public function confirm_reservation(Request $request)
    {
        $url = $this->get_url();
        $body_json = $request->all();
        $response = Http::post("$url/ConfirmaReserva", $body_json);
        if ($response->successful()) {
            return $response->json();
        } else {
            return $response->throw();
        }
    }

    public function confirm_passengers(Request $request)
    {
        $url = $this->get_url();
        $body_json = $request->all();
        $response = Http::post("$url/ConfirmaPasajeros", $body_json);
        if ($response->successful()) {
            return $response->json();
        } else {
            return $response->throw();
        }
    }

    public function reservationsAG(Request $request)
    {
        $params = [];

    if ($request->has('AG') && $request->AG !== null) {
        $params['AG'] = $request->AG;
    }
    if ($request->has('DESDEF') && $request->DESDEF !== null) {
        $params['DESDEF'] = $request->DESDEF;
    }
    if ($request->has('HASTAF') && $request->HASTAF !== null) {
        $params['HASTAF'] = $request->HASTAF;
    }
    if ($request->has('OPERADOR') && $request->OPERADOR !== null) {
        $params['OPERADOR'] = $request->OPERADOR;
    }
    if ($request->has('PRD') && $request->PRD !== null) {
        $params['PRD'] = $request->PRD;
    }
    if ($request->has('EST') && $request->EST !== null) {
        $params['EST'] = $request->EST;
    }
    if ($request->has('DESDEC') && $request->DESDEC !== null) {
        $params['DESDEC'] = $request->DESDEC;
    }
    if ($request->has('RSV') && $request->RSV !== null) {
        $params['RSV'] = $request->RSV;
    }
    if ($request->has('HASTAC') && $request->HASTAC !== null) {
        $params['HASTAC'] = $request->HASTAC;
    }

    $url = $this->get_url();
    $query = http_build_query($params);
    $response = Http::get("$url/ReservasAG?$query");

    if ($response->successful()) {
        return $response->json();
    } else {
        return $response->throw();
    }
    }

    public function ReservaxCodigo(Request $request)
    {
        $url = $this->get_url();
        $response = Http::get("$url/ReservaxCodigo?RSV=$request->RSV");   
        if ($response->successful()) {
            return $response->json();
        } else {
            return $response->throw();
        }
    }

    public function ProductosAG(Request $request)
    {
        $url = $this->get_url();
        $response = Http::get("$url/ProductosAG?FECHA=$request->FECHA");
        if ($response->successful()) {
            return $response->json();
        } else {
            return $response->throw();
        }
    }

    public function TurnosAG(Request $request)
    {
        $fecha_desde = $request->FECHAD;
        $fecha_hasta = $request->FECHAH;
        $excursion_id = $request->PRD;
        $url = $this->get_url();
        $response = Http::get("$url/TurnosAG?FECHAD=$fecha_desde&FECHAH=$fecha_hasta&PRD=$excursion_id");   
        if ($response->successful()) {
            return $response->json();
        } else {
            return $response->throw();
        }
    }

    // END HYA ENDPOINTS


    public function change_request(Request $request)
    {
        try {
            $request->validate([
                'reservation_number' => 'required',
                'id_user' => 'required',
                'agency_name' => 'required',
                'request' => 'required',
            ]);
    
            $user = AgencyUser::find($request->id_user);
            
            if(!$user)
                return response(["message" => "No se ha encontrado el usuario"], 422);

            $files = $request->attachments;

            // Mail::to("reservas@hieloyaventura.com")->send(new ReservationRequestChange($request, $user, $attachment));
            Mail::to("enzo100amarilla@gmail.com")->send(new ReservationRequestChange($request, $user, $files));
            
            return response(["message" => "Mail enviado con éxito!"], 200);
        } catch (\Throwable $th) {
            Log::debug(print_r([$th->getMessage(), $th->getLine()],  true));
            // return $th->getMessage();
            return response(["message" => "Mail no enviado"], 500);
        }
    }
}
