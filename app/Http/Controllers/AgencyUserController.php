<?php

namespace App\Http\Controllers;

use App\Exports\ServiciosDiariosExport;
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
use Maatwebsite\Excel\Facades\Excel;
use Tymon\JWTAuth\Http\Parser\AuthHeaders;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfReader;
 use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

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

        if ($request->agency_user_type_id == AgencyUserType::VENDEDOR) {
            $agency_user_seller_load = AgencyUserSellerLoad::where('agency_code', $request->agency_code)->first();
            $maximum_load = $agency_user_seller_load->seller_load ?? 5;
            $users_quantity = AgencyUser::where('agency_user_type_id', AgencyUserType::VENDEDOR)->where('agency_code', $request->agency_code)->count();
            if ($users_quantity >= $maximum_load) {
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

        if ($request->password)
            $user->password = Hash::make($request->password);

        $user->save();

        $user = AgencyUser::getAllDataUser($user->id);
        $message = "Usuario actualizado con exito";

        return response(compact("user", "message"));
    }

    public function terms_and_conditions()
    {
        if (Auth::guard('agency')->user()->agency_user_type_id == AgencyUserType::ADMIN) {
            $id = Auth::guard('agency')->user()->id;
        } else {
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
                return $query->where('agency_code', 'LIKE', '%' . $request->agency_code . '%');
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

            if (!isset($agency_user_seller_load)) {
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

    public function get_url()
    {
        $environment = config("app.environment");
        if ($environment == "DEV") {
            $url = "https://apihya.hieloyaventura.com/apihya_dev";
        } else {
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
        if ($request->has('HOTEL') && $request->HOTEL !== null) {
            $params['HOTEL'] = $request->HOTEL;
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
                'attachments' => 'nullable|array',
            ]);

            $user = AgencyUser::find($request->id_user);

            if (!$user)
                return response(["message" => "No se ha encontrado el usuario"], 422);


            // $files = $request->attachments;
            $files = $request->has('attachments') ? $request->attachments : [];

            Mail::to("reservas@hieloyaventura.com")->send(new ReservationRequestChange($request, $user, $files));

            return response(["message" => "Mail enviado con éxito!"], 200);
        } catch (\Throwable $th) {
            Log::debug(print_r([$th->getMessage(), $th->getLine()],  true));
            // return $th->getMessage();
            return response(["message" => "Mail no enviado"], 500);
        }
    }

    public function resumen_servicios_diarios(Request $request)
    {
        $date = $request->input('date');
        $agency_name = $request->input('agency_name');
        $data = $request->input('data', []);

        $pdf = new Fpdi();
        $pdf->SetAutoPageBreak(false);

        // Split data into groups
        $firstPageItems = array_slice($data, 0, 20);
        $remainingItems = array_slice($data, 20);
        $additionalPages = array_chunk($remainingItems, 24);

        // Load base template (first page)
        $templatePath1 = storage_path('app/public/bases_resumenes/BASE-H1.pdf');
        $pdf->setSourceFile($templatePath1);
        $tplIdx = $pdf->importPage(1);
        $pdf->addPage();
        $pdf->useTemplate($tplIdx, 0, 0, null, null, true);

        // Header: date, serve yourself to, provider
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY(8, 47); 
        $pdf->Write(0, $date);
        $pdf->SetXY(36, 47);
        $pdf->Write(0, 'Hielo & Aventura');
        $pdf->SetXY(72.9, 47);
        $pdf->Write(0, $agency_name);

        // Print data for the first page (max 20)
        $this->writeRows($pdf, $firstPageItems, 65.5);

        // Additional pages (24 records each)
        if (!empty($additionalPages)) {
            $templatePath2 = storage_path('app/public/bases_resumenes/BASE-H1-PAGE2.pdf');

            foreach ($additionalPages as $pageItems) {
                $pdf->setSourceFile($templatePath2);
                $tplIdx = $pdf->importPage(1);
                $pdf->addPage();
                $pdf->useTemplate($tplIdx, 0, 0, null, null, true);

                $this->writeRows($pdf, $pageItems, 31.1); // start higher on page 2
            }
        }

        // $content = $pdf->Output('PDFFFFFF.pdf', 'S');

        // return response($content)
        //     ->header('Content-Type', 'application/pdf')
        //     ->header('Content-Disposition', 'inline; filename="PDFFFFFF.pdf"');

        $filename = 'resumen-servicios-diarios-' . now()->format('Ymd_His') . '.pdf';
        $path = public_path('pdfs/' . $filename);

        if (!file_exists(public_path('pdfs'))) {
            mkdir(public_path('pdfs'), 0755, true);
        }
        
        $pdf->Output($path, 'F');

        return response()->json([
            'path' => 'pdfs/' . $filename,
            'url' => asset('pdfs/' . $filename)
        ]);
    }

    private function writeRows($pdf, $items, $startY)
    {
        $maxHotelLength = 20;
        $rowHeight = 8.83;
        $currentY = $startY;
        $defaultFont = ['Helvetica', '', 8.50];

        $reduceHotelFont = false;
        foreach ($items as $item) {
            if (strlen($item['hotel']) > $maxHotelLength) {
                $reduceHotelFont = true;
                break;
            }
        }

        foreach ($items as $item) {
            $x = 8;
            $pdf->SetFont(...$defaultFont);

            // Rva
            // $this->drawCell($pdf, $x, $currentY, 22, $rowHeight, $item['reservation_number'], [220, 220, 220]);
            $this->drawMultiLineCell($pdf, $x, $currentY, 22, $rowHeight, $item['reservation_number'], 16, [220, 220, 220]);

            // Pasajero
            // $this->drawCell($pdf, $x, $currentY, 42.2, $rowHeight, $item['pax'], [173, 216, 230]);
            $this->drawMultiLineCell($pdf, $x, $currentY, 42.2, $rowHeight, $item['pax'], 22, [173, 216, 230]);

            // Cant
            $this->drawCell($pdf, $x, $currentY, 16.8, $rowHeight, $item['number_of_passengers'], [255, 255, 153]);

            // Excursion (especial)
            $this->drawMultiLineCell($pdf, $x, $currentY, 33.5, $rowHeight, $item['excursion'], 16, [204, 255, 204]);
            // $this->drawCell($pdf, $x, $currentY, 33.5, $rowHeight, $item['excursion'], [204, 255, 204]);

            // Hotel (especial)
            $this->drawMultiLineCell($pdf, $x, $currentY, 38.5, $rowHeight, $item['hotel'], 20, [255, 204, 204], $reduceHotelFont);

            // Transfer
            $this->drawCell($pdf, $x, $currentY, 17.5, $rowHeight, $item['transfer'], [255, 229, 204]);

            // Hora
            $this->drawCell($pdf, $x, $currentY, 23.5, $rowHeight, $item['hour'], [230, 204, 255]);

            $currentY += $rowHeight;
        }
    }

    private function drawCell($pdf, &$x, $y, $width, $height, $text, $fillColor)
    {
        $pdf->SetFillColor(...$fillColor);
        $pdf->SetXY($x, $y);
        $pdf->Cell($width, $height, $text, 0, 0, 'C', false);
        $x += $width;
    }

    // private function drawMultiLineCell($pdf, &$x, $y, $width, $cellHeight, $text, $maxLength, $fillColor)
    // {
    //     $defaultFontSize = 8.81;
    //     $reducedFontSize = 7.2;
    //     $lineHeight = $cellHeight / 2;

    //     $fontSize = $defaultFontSize;
    //     if (strlen($text) > $maxLength) {
    //         $fontSize = $reducedFontSize;
    //         $text = wordwrap($text, ($maxLength + 2), "\n", false);
    //     }

    //     $pdf->SetFont('Helvetica', '', $fontSize);
    //     $pdf->SetFillColor(...$fillColor);

    //     // $lines = substr_count($text, "\n") + 1;
    //     // $totalTextHeight = $lineHeight * $lines;

    //     // // centrado vertical
    //     // $adjustedY = (strlen($text) <= $maxLength)
    //     //     ? $y + (($cellHeight - $totalTextHeight) / 2)
    //     //     : $y;

    //     $lines = substr_count($text, "\n") + 1;
    //     $totalTextHeight = $lineHeight * $lines;

    //     // centrado vertical universal
    //     $adjustedY = $y + (($cellHeight - $totalTextHeight) / 2);

    //     $pdf->SetXY($x, $adjustedY);
    //     $pdf->MultiCell($width, $lineHeight, $text, 0, 'C', false);

    //     $x += $width;
    //     $pdf->SetFont('Helvetica', '', $defaultFontSize);
    //     $pdf->SetXY($x, $y); // restaurar Y por si sigue otra celda
    // }

    private function drawMultiLineCell($pdf, &$x, $y, $width, $cellHeight, $text, $maxLength, $fillColor, $forceSmallFont = false)
    {
        $defaultFontSize = 8.50;
        $reducedFontSize = 7.2;
        $lineHeight = $cellHeight / 2;

        $fontSize = $forceSmallFont ? $reducedFontSize : $defaultFontSize;
        if ($forceSmallFont || strlen($text) > $maxLength) {
            $text = wordwrap($text, ($maxLength + 2), "\n", false);
        }

        $pdf->SetFont('Helvetica', '', $fontSize);
        $pdf->SetFillColor(...$fillColor);

        $lines = substr_count($text, "\n") + 1;
        $totalTextHeight = $lineHeight * $lines;
        $adjustedY = $y + (($cellHeight - $totalTextHeight) / 2);

        $pdf->SetXY($x, $adjustedY);
        $pdf->MultiCell($width, $lineHeight, $text, 0, 'C', false);

        $x += $width;
        $pdf->SetFont('Helvetica', '', $defaultFontSize);
        $pdf->SetXY($x, $y);
    }


    // public function resumen_servicios_diarios_excel(Request $request)
    // {
    //     $data = $request->input('data', []);
    //     $export = new ServiciosDiariosExport($data);

    //     $filename = 'resumen-servicios-diarios-' . now()->format('Ymd_His') . '.xlsx';
    //     $path = public_path('excels/' . $filename);

    //     if (!file_exists(public_path('excels'))) {
    //         mkdir(public_path('excels'), 0755, true);
    //     }

    //     $excelData = Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX);

    //     if (File::put($path, $excelData) === false) {
    //         return response()->json(['error' => 'No se pudo guardar el archivo'], 500);
    //     }

    //     return response()->json([
    //         'path' => 'excels/' . $filename,
    //         'url' => asset('excels/' . $filename),
    //     ]);
    // }

    public function resumen_servicios_diarios_excel(Request $request)
    {
        $data = $request->data;

        $filename = 'resumen-servicios-diarios-' . now()->format('Ymd_His') . '.csv';
        $fullPath = public_path('excels/' . $filename);

        // Nos aseguramos de que el directorio exista
        if (!file_exists(public_path('excels'))) {
            mkdir(public_path('excels'), 0755, true);
        }

        $handle = fopen($fullPath, 'w');

        // UTF-8 BOM
        fwrite($handle, "\xEF\xBB\xBF");

        // Cabeceras
        fputcsv($handle, [
            'Nro Reserva',
            'Pasajero',
            'Cant',
            'Excursion',
            'Hotel',
            'Transfer',
            'Hora',
        ], ';');

        // Filas
        foreach ($data as $item) {
            fputcsv($handle, [
                $item['reservation_number'],
                $item['pax'],
                $item['number_of_passengers'],
                $item['excursion'],
                $item['hotel'],
                $item['transfer'],
                $item['hour'],
            ], ';');
        }

        fclose($handle);

        return response()->json([
            'message' => 'Archivo generado exitosamente.',
            'path' => 'excels/' . $filename,
            'url' => asset('excels/' . $filename),
        ]);
    }

}
