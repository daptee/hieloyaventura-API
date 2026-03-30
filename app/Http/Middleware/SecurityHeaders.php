<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Previene que el navegador interprete archivos con un MIME type diferente al declarado
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Evita que la API sea embebida en iframes (clickjacking)
        $response->headers->set('X-Frame-Options', 'DENY');

        // Protección XSS legacy para navegadores antiguos
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Fuerza HTTPS por 1 año e incluye subdominios
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');

        // Controla qué información de referencia se envía al navegar
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Deshabilita features del navegador que no necesita esta API
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        // Oculta detalles de la infraestructura (previene fingerprinting)
        $response->headers->remove('Server');
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('X-AspNet-Version');
        $response->headers->set('Server', 'Web Server');

        return $response;
    }
}
