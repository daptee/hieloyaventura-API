<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\AgencyUser;
use App\Models\AgencyUserType;
use App\Models\Module;
use App\Models\Notification;
use App\Models\NotificationAgency;
use App\Models\NotificationRead;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    // Longitud del recorte de texto para la columna "prevista" en el listado
    const PREVIEW_LENGTH = 100;

    /**
     * POST /api/admin/notifications
     * Crea una nueva notificación y la asocia a las agencias destinatarias.
     */
    public function store(Request $request)
    {
        if ($error = $this->requireModule(Module::NOTIFICACIONES)) return $error;

        $request->validate([
            'title'                => 'required|string|max:255',
            'body'                 => 'required|string',
            'recipients_type'      => 'required|in:admins,all',
            'send_to_all_agencies' => 'required|boolean',
            'agencies'             => 'required_if:send_to_all_agencies,false|array|min:1',
            'agencies.*'           => 'string|exists:agencies,agency_code',
        ]);

        DB::beginTransaction();
        try {
            $notification = Notification::create([
                'title'                => $request->title,
                'body'                 => $request->body,
                'recipients_type'      => $request->recipients_type,
                'send_to_all_agencies' => $request->send_to_all_agencies,
            ]);

            if (!$request->send_to_all_agencies && !empty($request->agencies)) {
                $records = array_map(fn($code) => [
                    'notification_id' => $notification->id,
                    'agency_code'     => $code,
                    'created_at'      => now(),
                ], $request->agencies);

                NotificationAgency::insert($records);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return response(['message' => 'Error al crear la notificación.', 'error' => $e->getMessage()], 500);
        }

        $notification->load('agencies');

        return response([
            'message'      => 'Notificación creada exitosamente.',
            'notification' => $notification,
        ], 201);
    }

    /**
     * GET /api/admin/notifications
     * Listado paginado con filtros de búsqueda y rango de fechas.
     *
     * Query params:
     *   q          string  Búsqueda por coincidencia en el título
     *   date_from  date    Fecha mínima de creación (YYYY-MM-DD)
     *   date_to    date    Fecha máxima de creación (YYYY-MM-DD)
     *   page       int     Página (default: 1)
     */
    public function index(Request $request)
    {
        if ($error = $this->requireModule(Module::NOTIFICACIONES)) return $error;

        $query = Notification::query()
            ->when($request->q, fn($q) => $q->where('title', 'LIKE', '%' . $request->q . '%'))
            ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->orderBy('id', 'desc');

        $total          = $query->count();
        $total_per_page = 30;
        $paginated      = $query->paginate($total_per_page);
        $current_page   = $paginated->currentPage();
        $last_page      = $paginated->lastPage();

        $notifications = $paginated->getCollection()->map(function (Notification $notification) {
            $recipients_count = $this->getRecipientsCount($notification);
            $reads_count      = NotificationRead::where('notification_id', $notification->id)->count();

            return [
                'id'               => $notification->id,
                'title'            => $notification->title,
                'preview'          => $this->buildPreview($notification->body),
                'created_at'       => $notification->created_at,
                'recipients_count' => $recipients_count,
                'reads_count'      => $reads_count,
            ];
        });

        return response(compact('notifications', 'total', 'total_per_page', 'current_page', 'last_page'));
    }

    /**
     * GET /api/admin/notifications/{id}
     * Detalle completo de una notificación: texto, fecha, agencias destinatarias
     * con porcentaje de lectura y listado de usuarios por agencia.
     */
    public function show(int $id)
    {
        if ($error = $this->requireModule(Module::NOTIFICACIONES)) return $error;

        $notification = Notification::with('agencies')->findOrFail($id);

        $agencyCodes = $notification->send_to_all_agencies
            ? Agency::pluck('agency_code')->toArray()
            : $notification->agencies->pluck('agency_code')->toArray();

        $agencies_data = [];

        foreach ($agencyCodes as $agencyCode) {
            $usersQuery = AgencyUser::where('agency_code', $agencyCode)
                ->where('active', 1)
                ->whereNull('deleted_at');

            if ($notification->recipients_type === 'admins') {
                $usersQuery->where('agency_user_type_id', AgencyUserType::ADMIN);
            }

            $users   = $usersQuery->get();
            $userIds = $users->pluck('id')->toArray();

            // Lecturas indexadas por agency_user_id para búsqueda O(1)
            $reads = NotificationRead::where('notification_id', $notification->id)
                ->whereIn('agency_user_id', $userIds)
                ->get()
                ->keyBy('agency_user_id');

            $total_users    = count($userIds);
            $read_count     = $reads->count();
            $read_percentage = $total_users > 0
                ? round(($read_count / $total_users) * 100, 1)
                : 0;

            $agencies_data[] = [
                'agency_code'     => $agencyCode,
                'total_users'     => $total_users,
                'read_count'      => $read_count,
                'read_percentage' => $read_percentage,
                'users'           => $users->map(function (AgencyUser $user) use ($reads) {
                    $read = $reads->get($user->id);
                    return [
                        'id'        => $user->id,
                        'name'      => $user->name,
                        'last_name' => $user->last_name,
                        'email'     => $user->email,
                        'read'      => !is_null($read),
                        'read_at'   => $read ? $read->read_at : null,
                    ];
                })->values(),
            ];
        }

        return response([
            'notification' => [
                'id'                   => $notification->id,
                'title'                => $notification->title,
                'body'                 => $notification->body,
                'recipients_type'      => $notification->recipients_type,
                'send_to_all_agencies' => $notification->send_to_all_agencies,
                'created_at'           => $notification->created_at,
                'agencies'             => $agencies_data,
            ],
        ]);
    }

    /**
     * POST /api/notifications/{id}/read
     * Marca una notificación como leída para el usuario de agencia autenticado.
     * El front llama a este endpoint cuando el usuario abre la notificación.
     */
    public function markAsRead(int $id)
    {
        $agencyUser = Auth()->guard('agency')->user();

        if (!$agencyUser) {
            return response(['message' => 'No autenticado.'], 401);
        }

        $notification = Notification::findOrFail($id);

        // Verifica que la notificación le corresponda al usuario
        if (!$this->notificationBelongsToUser($notification, $agencyUser)) {
            return response(['message' => 'No tiene acceso a esta notificación.'], 403);
        }

        // updateOrCreate evita duplicados por la UNIQUE KEY
        NotificationRead::firstOrCreate([
            'notification_id' => $notification->id,
            'agency_user_id'  => $agencyUser->id,
        ], [
            'read_at' => now(),
        ]);

        return response(['message' => 'Notificación marcada como leída.']);
    }

    // -------------------------------------------------------------------------
    // Endpoints para portal de agencias
    // -------------------------------------------------------------------------

    /**
     * GET /api/agency/notifications
     * Listado paginado de notificaciones para el usuario de agencia autenticado.
     *
     * Query params:
     *   unread  bool  1 = solo no leídas, 0 u omitido = todas
     *   page    int   Página (default: 1)
     *
     * Ordenamiento: no leídas primero, luego más nueva a más vieja.
     * Cada item retorna: id, title, preview, created_at, read (bool), read_at.
     */
    public function agencyIndex(Request $request)
    {
        $agencyUser = Auth::guard('agency')->user();

        $query = Notification::query()
            ->select('notifications.*', 'notification_reads.read_at')
            ->leftJoin('notification_reads', function ($join) use ($agencyUser) {
                $join->on('notification_reads.notification_id', '=', 'notifications.id')
                     ->where('notification_reads.agency_user_id', $agencyUser->id);
            })
            // Filtro por tipo de destinatario: vendedores/comerciales no ven las de solo admins
            ->where(function ($q) use ($agencyUser) {
                $q->where('notifications.recipients_type', 'all');
                if ($agencyUser->agency_user_type_id === AgencyUserType::ADMIN) {
                    $q->orWhere('notifications.recipients_type', 'admins');
                }
            })
            // Filtro por agencia destinataria
            ->where(function ($q) use ($agencyUser) {
                $q->where('notifications.send_to_all_agencies', 1)
                  ->orWhereExists(function ($sub) use ($agencyUser) {
                      $sub->select(DB::raw(1))
                          ->from('notification_agencies')
                          ->whereColumn('notification_agencies.notification_id', 'notifications.id')
                          ->where('notification_agencies.agency_code', $agencyUser->agency_code);
                  });
            })
            // Filtro opcional: solo no leídas
            ->when($request->unread, fn($q) => $q->whereNull('notification_reads.read_at'))
            // No leídas primero (read_at IS NULL = 0 va antes que 1), luego más nueva
            ->orderByRaw('(notification_reads.read_at IS NOT NULL) ASC')
            ->orderBy('notifications.created_at', 'desc');

        $total          = $query->count();
        $total_per_page = 30;
        $paginated      = $query->paginate($total_per_page);
        $current_page   = $paginated->currentPage();
        $last_page      = $paginated->lastPage();

        $notifications = $paginated->getCollection()->map(function ($row) {
            return [
                'id'         => $row->id,
                'title'      => $row->title,
                'preview'    => $this->buildPreview($row->body),
                'created_at' => $row->created_at,
                'read'       => !is_null($row->read_at),
                'read_at'    => $row->read_at,
            ];
        });

        return response(compact('notifications', 'total', 'total_per_page', 'current_page', 'last_page'));
    }

    /**
     * GET /api/agency/notifications/{id}
     * Detalle de una notificación para el usuario de agencia autenticado.
     * Retorna: id, title, body (HTML completo), created_at, read, read_at.
     */
    public function agencyShow(int $id)
    {
        $agencyUser = Auth::guard('agency')->user();

        $notification = Notification::findOrFail($id);

        if (!$this->notificationBelongsToUser($notification, $agencyUser)) {
            return response(['message' => 'No tiene acceso a esta notificación.'], 403);
        }

        $read = NotificationRead::where('notification_id', $notification->id)
            ->where('agency_user_id', $agencyUser->id)
            ->first();

        return response([
            'notification' => [
                'id'         => $notification->id,
                'title'      => $notification->title,
                'body'       => $notification->body,
                'created_at' => $notification->created_at,
                'read'       => !is_null($read),
                'read_at'    => $read ? $read->read_at : null,
            ],
        ]);
    }

    // -------------------------------------------------------------------------
    // Helpers privados
    // -------------------------------------------------------------------------

    /**
     * Genera el texto de vista previa: elimina tags HTML, decodifica entidades
     * y recorta al largo configurado.
     */
    private function buildPreview(string $body): string
    {
        $plain = strip_tags($body);
        $plain = html_entity_decode($plain, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $plain = preg_replace('/\s+/', ' ', trim($plain));

        if (mb_strlen($plain) <= self::PREVIEW_LENGTH) {
            return $plain;
        }

        return mb_substr($plain, 0, self::PREVIEW_LENGTH) . '...';
    }

    /**
     * Cuenta los usuarios de agencia destinatarios de una notificación.
     */
    private function getRecipientsCount(Notification $notification): int
    {
        $query = AgencyUser::where('active', 1)->whereNull('deleted_at');

        if ($notification->recipients_type === 'admins') {
            $query->where('agency_user_type_id', AgencyUserType::ADMIN);
        }

        if (!$notification->send_to_all_agencies) {
            $agencyCodes = NotificationAgency::where('notification_id', $notification->id)
                ->pluck('agency_code');
            $query->whereIn('agency_code', $agencyCodes);
        }

        return $query->count();
    }

    /**
     * Verifica que una notificación corresponda al usuario de agencia dado.
     * Chequea agencia y tipo de usuario según recipients_type.
     */
    private function notificationBelongsToUser(Notification $notification, AgencyUser $agencyUser): bool
    {
        // Verificar tipo de destinatario
        if ($notification->recipients_type === 'admins'
            && $agencyUser->agency_user_type_id !== AgencyUserType::ADMIN) {
            return false;
        }

        // Si la notificación no es para todas las agencias, verificar que su agencia esté incluida
        if (!$notification->send_to_all_agencies) {
            $included = NotificationAgency::where('notification_id', $notification->id)
                ->where('agency_code', $agencyUser->agency_code)
                ->exists();
            if (!$included) {
                return false;
            }
        }

        return true;
    }
}
