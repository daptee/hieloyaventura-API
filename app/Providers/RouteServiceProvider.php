<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::prefix('api')
                ->middleware('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    private function logRateLimited(string $type, Request $request): void
    {
        $logPath = storage_path('logs/security/failed-logins-' . now()->format('Y-m') . '.log');
        $logger = Log::build([
            'driver' => 'single',
            'path'   => $logPath,
            'level'  => 'warning',
        ]);
        $logger->warning('Rate limit superado', [
            'type'       => $type,
            'reason'     => 'demasiados intentos - rate limit',
            'email'      => $request->input('email'),
            'ip'         => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp'  => now()->toDateTimeString(),
        ]);
    }

    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Login web y agencias: 10 intentos por minuto por IP
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip())->response(function () use ($request) {
                $this->logRateLimited('login', $request);
                return response()->json([
                    'message' => 'Demasiados intentos de inicio de sesión. Por favor, intentá de nuevo en un minuto.'
                ], 429);
            });
        });

        // Login admin: más estricto, 5 intentos por minuto por IP
        RateLimiter::for('admin-login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip())->response(function () use ($request) {
                $this->logRateLimited('admin', $request);
                return response()->json([
                    'message' => 'Demasiados intentos de inicio de sesión. Por favor, intentá de nuevo en un minuto.'
                ], 429);
            });
        });

        // Recuperación de contraseña: 5 intentos por minuto por IP
        RateLimiter::for('password-recovery', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip())->response(function () use ($request) {
                $this->logRateLimited('password-recovery', $request);
                return response()->json([
                    'message' => 'Demasiadas solicitudes de recuperación de contraseña. Por favor, intentá de nuevo en un minuto.'
                ], 429);
            });
        });
    }
}
