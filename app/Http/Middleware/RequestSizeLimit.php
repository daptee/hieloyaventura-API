<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequestSizeLimit
{
    // Máximo 10MB para uploads, 1MB para requests de JSON/API estándar
    protected int $maxJsonBytes    = 1 * 1024 * 1024;   // 1 MB
    protected int $maxUploadBytes  = 10 * 1024 * 1024;  // 10 MB

    public function handle(Request $request, Closure $next)
    {
        $contentLength = (int) $request->header('Content-Length', 0);
        $contentType   = $request->header('Content-Type', '');

        $isFileUpload = str_contains($contentType, 'multipart/form-data');
        $maxBytes     = $isFileUpload ? $this->maxUploadBytes : $this->maxJsonBytes;

        if ($contentLength > $maxBytes) {
            return response()->json([
                'message' => 'El tamaño del request supera el límite permitido.',
            ], 413);
        }

        return $next($request);
    }
}
