<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
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
    ];

    /**
     * User-Agents que indican automatización pero pueden ser legítimos (herramientas de desarrollo).
     * Se loguean con severidad menor.
     */
    protected array $suspiciousPatterns = [
        'python-requests',
        'python-urllib',
        'java/',
        'curl/',
        'libwww-perl',
        'okhttp',
    ];

    public function handle(Request $request, Closure $next)
    {
        $ua = strtolower($request->userAgent() ?? '');

        if (empty($ua)) {
            $this->log($request, 'sin user-agent', 'warning');
            return $next($request);
        }

        foreach ($this->knownBotPatterns as $pattern) {
            if (str_contains($ua, $pattern)) {
                $this->log($request, "bot conocido: {$pattern}", 'warning');
                return $next($request);
            }
        }

        foreach ($this->suspiciousPatterns as $pattern) {
            if (str_contains($ua, $pattern)) {
                $this->log($request, "cliente sospechoso: {$pattern}", 'info');
                return $next($request);
            }
        }

        return $next($request);
    }

    private function log(Request $request, string $reason, string $level): void
    {
        $logPath = storage_path('logs/security/bot-detections-' . now()->format('Y-m') . '.log');
        $logger  = Log::build([
            'driver' => 'single',
            'path'   => $logPath,
            'level'  => 'info',
        ]);

        $logger->{$level}('Bot/scraper detectado', [
            'reason'     => $reason,
            'ip'         => $request->ip(),
            'user_agent' => $request->userAgent(),
            'method'     => $request->method(),
            'path'       => $request->path(),
            'query'      => $request->query() ?: null,
            'timestamp'  => now()->toDateTimeString(),
        ]);
    }
}
