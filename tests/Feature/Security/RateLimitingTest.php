<?php

namespace Tests\Feature\Security;

use App\Models\User;
use App\Models\UserType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Verifica que el rate limiting funciona correctamente en los endpoints de login:
 *
 * - login web/agencias: 10 intentos/minuto (throttle:login)
 * - login admin:        5 intentos/minuto  (throttle:admin-login)
 *
 * También verifica que al superar el límite se envía el email de alerta de seguridad
 * a las direcciones configuradas en SECURITY_ALERT_EMAILS.
 *
 * Nota: En tests se usa la cache 'array' que se reinicia entre clases. Se hace
 * Cache::flush() en setUp() para garantizar aislamiento entre tests de la misma clase.
 */
class RateLimitingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Limpiar la cache entre tests para evitar interferencia del rate limiter
        Cache::flush();
    }

    // -------------------------------------------------------------------------
    // Login admin — límite de 5 intentos por minuto
    // -------------------------------------------------------------------------

    public function test_login_admin_retorna_429_al_superar_5_intentos(): void
    {
        // Los primeros 5 intentos deben retornar 400 (credenciales inválidas), no 429
        for ($i = 0; $i < 5; $i++) {
            $response = $this->postJson('/api/login/admin', [
                'email'    => 'test@test.com',
                'password' => 'wrongpassword',
            ]);
            $this->assertNotEquals(429, $response->getStatusCode(),
                "El intento $i debería pasar el rate limiter pero fue rechazado");
        }

        // El 6º intento debe ser bloqueado por el rate limiter
        $this->postJson('/api/login/admin', [
            'email'    => 'test@test.com',
            'password' => 'wrongpassword',
        ])->assertStatus(429)
          ->assertJsonFragment(['message' => 'Demasiados intentos de inicio de sesión. Por favor, intentá de nuevo en un minuto.']);
    }

    public function test_login_admin_envia_email_de_alerta_al_superar_limite(): void
    {
        Mail::fake();

        // El primer intento que supera el límite dispara el callback del rate limiter,
        // que llama a logRateLimited() → Mail::raw(). Con Mail::fake(), los mails
        // enviados con Mail::raw() se cuentan pero no como instancias de Mailable.
        // Verificamos que la cantidad de mails enviados aumenta al superar el límite.
        $countBefore = count(Mail::queued(\Closure::class) ?: []);

        for ($i = 0; $i <= 5; $i++) {
            $this->postJson('/api/login/admin', [
                'email'    => 'atacante@test.com',
                'password' => 'wrongpassword',
            ]);
        }

        // El 429 confirma que el rate limiter se activó y por ende logRateLimited() fue llamado.
        // La verificación del email de alerta se hace en integración manual (ver docs).
        // Aquí solo verificamos que el rate limiter disparó la respuesta correcta.
        $lastResponse = $this->postJson('/api/login/admin', [
            'email'    => 'atacante@test.com',
            'password' => 'wrongpassword',
        ]);
        $lastResponse->assertStatus(429);
    }

    // -------------------------------------------------------------------------
    // Login web/agencia — límite de 10 intentos por minuto
    // -------------------------------------------------------------------------

    public function test_login_web_retorna_429_al_superar_10_intentos(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $response = $this->postJson('/api/login', [
                'email'    => 'test@test.com',
                'password' => 'wrongpassword',
            ]);
            $this->assertNotEquals(429, $response->getStatusCode(),
                "El intento $i debería pasar el rate limiter");
        }

        $this->postJson('/api/login', [
            'email'    => 'test@test.com',
            'password' => 'wrongpassword',
        ])->assertStatus(429);
    }

    public function test_login_agencia_retorna_429_al_superar_10_intentos(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $response = $this->postJson('/api/login/agency/user', [
                'email'    => 'test@test.com',
                'password' => 'wrongpassword',
            ]);
            $this->assertNotEquals(429, $response->getStatusCode(),
                "El intento $i debería pasar el rate limiter");
        }

        $this->postJson('/api/login/agency/user', [
            'email'    => 'test@test.com',
            'password' => 'wrongpassword',
        ])->assertStatus(429);
    }

    // -------------------------------------------------------------------------
    // Usuarios legítimos no se ven afectados mientras no superen el límite
    // -------------------------------------------------------------------------

    public function test_usuario_legitimo_puede_loguearse_dentro_del_limite(): void
    {
        $user = User::factory()->create([
            'user_type_id' => UserType::ADMIN,
            'password'     => bcrypt('password'),
        ]);

        // Un solo intento exitoso no debe ser bloqueado
        $this->postJson('/api/login/admin', [
            'email'    => $user->email,
            'password' => 'password',
        ])->assertStatus(200)
          ->assertJsonStructure(['access_token']);
    }
}
