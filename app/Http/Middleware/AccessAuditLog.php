<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AccessAuditLog
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Solo loguear si el status es relevante para auditoría
        $status = $response->getStatusCode();
        if (!in_array($status, [200, 201, 400, 401, 403, 422, 429])) {
            return $response;
        }

        $this->log($request, $status);

        return $response;
    }

    private function log(Request $request, int $status): void
    {
        $logPath = storage_path('logs/security/access-audit-' . now()->format('Y-m') . '.log');
        $logger  = Log::build(['driver' => 'single', 'path' => $logPath, 'level' => 'info']);

        $user = null;
        try {
            if (\Auth::guard('web')->check()) {
                $u    = \Auth::guard('web')->user();
                $user = ['type' => 'admin', 'id' => $u->id, 'email' => $u->email];
            } elseif (\Auth::guard('agency')->check()) {
                $u    = \Auth::guard('agency')->user();
                $user = ['type' => 'agency', 'id' => $u->id, 'email' => $u->email, 'agency_code' => $u->agency_code];
            }
        } catch (\Throwable $e) {
            // Ignorar errores al leer el usuario autenticado
        }

        $logger->info('Acceso a endpoint sensible', [
            'method'     => $request->method(),
            'path'       => $request->path(),
            'status'     => $status,
            'ip'         => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user'       => $user,
            'timestamp'  => now()->toDateTimeString(),
        ]);
    }
}
