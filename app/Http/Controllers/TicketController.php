<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    public $model = Ticket::class;
    public $s = "ticket"; //sustantivo singular
    public $sp = "tickets"; //sustantivo plural
    public $ss = "ticket/s"; //sustantivo sigular/plural
    public $v = "o"; //verbo ej:encontrado/a
    public $pr = "el"; //preposicion singular
    public $prp = "los"; //preposicion plural
    
    public function index(Request $request)
    {
        $query = $this->model::with($this->model::INDEX)
                ->when($request->user !== null, function ($query) use ($request) {
                    return $query->whereHas('user', function ($q) use ($request) {
                        $q->where('email', 'LIKE', '%'.$request->user.'%');
                    });
                })
                ->when($request->creation_date_from !== null, function ($query) use ($request) {
                    return $query->where('created_at', '>=', $request->creation_date_from);
                }) 
                ->when($request->creation_date_to !== null, function ($query) use ($request) {
                    return $query->where('created_at', '<=', $request->creation_date_to);
                })
                ->orderBy('id', 'desc');
    
        $total = $query->count();
        $total_per_page = 30;
        $data = $query->paginate($total_per_page);
        $current_page = $request->page ?? $data->currentPage();
        $last_page = $data->lastPage();

        $tickets = $data;

        return response(compact("tickets", "total", "total_per_page", "current_page", "last_page"));

        return response(compact("tickets"));
    }

    public function store(Request $request)
    {
        $request->validate([
            'reservation_id' => 'required',
            'user_id' => 'required',
            'status_id' => 'required',
        ]);

        $ticket_exist = $this->model::where('reservation_id', $request->reservation_id)->where('user_id', $request->user_id)->where('status_id', $request->status_id)->first();

        if($ticket_exist)
            return response()->json(['message' => 'Registro ya existente.'], 400);

        $ticket = new $this->model($request->all());
        $ticket->save();

        $ticket = $this->model::getAllDataTicket($ticket->id);

        return response(compact("ticket"));
    }

    public function message(Request $request)
    {
        $request->validate([
            'ticket_id' => 'required',
            'message' => 'required',
            'user_id' => 'required',
        ]);

        $ticket_message = new TicketMessage($request->all());

        if($request->file){
            $file = $request->file;
            $fileName   = Str::random(5) . time() . '.' . $file->extension();
            $file->move(public_path("tickets/files"),$fileName);
            $path = "/tickets/files/$fileName";
            
            $ticket_message->file = $path;
        }

        $ticket_message->save();

        return response(compact("ticket_message"));
    }

    public function change_status(Request $request)
    {
        $request->validate([
            "ticket_id" => ['required', 'integer', Rule::exists('tickets', 'id')],
            "status_id" => ['required', 'integer', Rule::exists('tickets_status', 'id')],
        ]);

        $ticket = $this->model::find($request->ticket_id);
        $ticket->status_id = $request->status_id;
        $ticket->save();

        $ticket = $this->model::getAllDataTicket($ticket->id);
        return response(compact("ticket"));
    }
}
