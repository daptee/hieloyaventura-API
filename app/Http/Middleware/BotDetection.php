<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BotDetection
{
    /**
     * User-Agent strings (en minúsculas) que identifican bots / scrapers conocidos.
     * Se usa coincidencia parcial (str_contains).
     */
    protected array $knownBotPatterns = [
        'patagonia chic',   // bot competidor identificado en logs de acceso
        'scrapy',
        'selenium',
        'puppeteer',
        'playwright',
        'headlesschrome',
        'phantomjs',
        'httrack',
        'wget/',
        'masscan',
        'nikto',
        'sqlmap',
        'nmap',
        'zgrab',
        'go-http-client',   // cliente Go genérico usado frecuentemente en scraping masivo
        'curl/',            // scanner masivo identificado en logs (185.177.72.x)
        'python-requests',  // scraper automatizado
        'python-urllib',    // scraper automatizado
        'libwww-perl',      // scraper automatizado
        'okhttp',           // cliente Android usado en scrapers
    ];

    /**
     * User-Agents que indican automatización pero pueden ser legítimos (herramientas de desarrollo).
     * Se loguean con severidad menor.
     */
    protected array $suspiciousPatterns = [
        'java/',  // puede ser SDK de Android legítimo; solo se loguea
    ];

    public function handle(Request $request, Closure $next)
    {
        $ua = strtolower($request->userAgent() ?? '');

        if (empty($ua)) {
            $this->log($request, 'sin user-agent', 'warning', true);
            return response()->json(['message' => 'Forbidden'], 403);
        }

        foreach ($this->knownBotPatterns as $pattern) {
            if (str_contains($ua, $pattern)) {
                $this->log($request, "bot conocido: {$pattern}", 'warning', true);
                return response()->json(['message' => 'Forbidden'], 403);
            }
        }

        foreach ($this->suspiciousPatterns as $pattern) {
            if (str_contains($ua, $pattern)) {
                $this->log($request, "cliente sospechoso: {$pattern}", 'info', false);
                return $next($request);
            }
        }

        return $next($request);
    }

    private function log(Request $request, string $reason, string $level, bool $blocked = true): void
    {
        $logPath = storage_path('logs/security/bot-detections-' . now()->format('Y-m-d') . '.log');
        $logger  = Log::build([
            'driver' => 'single',
            'path'   => $logPath,
            'level'  => 'info',
        ]);

        $logger->{$level}('Bot/scraper detectado', [
            'reason'         => $reason,
            'blocked'        => $blocked,
            'ip'             => $request->ip(),
            'x_forwarded_for'=> $request->header('X-Forwarded-For'),
            'user_agent'     => $request->userAgent(),
            'method'         => $request->method(),
            'protocol'       => $request->getScheme(),
            'host'           => $request->getHost(),
            'path'           => $request->path(),
            'query'          => $request->query() ?: null,
            'referer'        => $request->header('Referer'),
            'accept'         => $request->header('Accept'),
            'headers_count'  => count($request->headers->all()),
            'timestamp'      => now()->toDateTimeString(),
        ]);

        // Email alert solo para bots conocidos (nivel warning), máximo 1 por patrón por hora
        if ($level === 'warning') {
            $cacheKey = 'bot_alert_' . md5($reason);
            if (Cache::has($cacheKey)) {
                return;
            }
            Cache::put($cacheKey, true, now()->addHour());

            $alertEmails = array_filter(array_map(
                'trim',
                explode(',', env('SECURITY_ALERT_EMAILS', ''))
            ));

            if (!empty($alertEmails)) {
                $subject = "[HyA Security] Bot/scraper detectado";
                $body    = "Se detectó actividad sospechosa de bot o scraper.\n\n"
                    . "Razón: " . $reason . "\n"
                    . "IP: " . $request->ip() . "\n"
                    . "User-Agent: " . $request->userAgent() . "\n"
                    . "Método: " . $request->method() . "\n"
                    . "Path: " . $request->path() . "\n"
                    . "Timestamp: " . now()->toDateTimeString() . "\n";

                foreach ($alertEmails as $email) {
                    try {
                        \Illuminate\Support\Facades\Mail::raw($body, function ($msg) use ($email, $subject) {
                            $msg->to($email)->subject($subject);
                        });
                    } catch (\Throwable $e) {
                        // No interrumpir el flujo si falla el envío de email
                    }
                }
            }
        }
    }
}
