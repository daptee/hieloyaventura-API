<?php

namespace Tests\Feature\Agency;

use App\Mail\AgencyOtpMailable;
use App\Models\AgencyUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Tests del flujo de autenticación con doble factor (2FA) para usuarios de agencia.
 *
 * Paso 1: POST /api/login/agency/user  → valida credenciales, envía OTP por email
 * Paso 2: POST /api/login/agency/verify-otp → valida OTP, devuelve JWT
 */
class AuthAgencyTwoFactorTest extends TestCase
{
    use RefreshDatabase;

    private string $loginEndpoint     = '/api/login/agency/user';
    private string $verifyOtpEndpoint = '/api/login/agency/verify-otp';

    // -------------------------------------------------------------------------
    // Paso 1: login / envío de OTP
    // -------------------------------------------------------------------------

    public function test_login_con_credenciales_validas_envia_otp_y_retorna_pending_2fa(): void
    {
        Mail::fake();

        $user = AgencyUser::factory()->create(['password' => bcrypt('password')]);

        $response = $this->postJson($this->loginEndpoint, [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['pending_2fa' => true])
                 ->assertJsonMissing(['access_token']);

        Mail::assertSent(AgencyOtpMailable::class, fn ($mail) =>
            $mail->hasTo($user->email) && $mail->type === 'login'
        );

        $this->assertNotNull($user->fresh()->otp_code);
        $this->assertNotNull($user->fresh()->otp_expires_at);
    }

    public function test_login_falla_con_password_incorrecto(): void
    {
        Mail::fake();

        $user = AgencyUser::factory()->create(['password' => bcrypt('password')]);

        $this->postJson($this->loginEndpoint, [
            'email'    => $user->email,
            'password' => 'wrong',
        ])->assertStatus(400)
          ->assertJsonFragment(['message' => 'Email y/o clave no válidos.']);

        Mail::assertNotSent(AgencyOtpMailable::class);
    }

    public function test_login_falla_si_usuario_esta_inactivo(): void
    {
        Mail::fake();

        $user = AgencyUser::factory()->inactive()->create(['password' => bcrypt('password')]);

        $this->postJson($this->loginEndpoint, [
            'email'    => $user->email,
            'password' => 'password',
        ])->assertStatus(400)
          ->assertJsonFragment(['message' => 'Email y/o clave no válidos.']);

        Mail::assertNotSent(AgencyOtpMailable::class);
    }

    public function test_login_falla_si_email_no_existe(): void
    {
        $this->postJson($this->loginEndpoint, [
            'email'    => 'noexiste@test.com',
            'password' => 'password',
        ])->assertStatus(400);
    }

    public function test_login_falla_si_faltan_campos_requeridos(): void
    {
        $this->postJson($this->loginEndpoint, ['email' => 'test@test.com'])
             ->assertStatus(422);

        $this->postJson($this->loginEndpoint, ['password' => 'password'])
             ->assertStatus(422);
    }

    // -------------------------------------------------------------------------
    // Paso 2: verificación de OTP
    // -------------------------------------------------------------------------

    public function test_verify_otp_correcto_devuelve_jwt(): void
    {
        $user = AgencyUser::factory()->create([
            'password'        => bcrypt('password'),
            'otp_code'        => '123456',
            'otp_expires_at'  => now()->addMinutes(10),
        ]);

        $response = $this->postJson($this->verifyOtpEndpoint, [
            'email' => $user->email,
            'otp'   => '123456',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['access_token', 'token_type', 'expires_in', 'data'])
                 ->assertJson(['token_type' => 'Bearer']);

        // OTP debe limpiarse después de usarse
        $this->assertNull($user->fresh()->otp_code);
        $this->assertNull($user->fresh()->otp_expires_at);
    }

    public function test_verify_otp_incorrecto_retorna_400(): void
    {
        $user = AgencyUser::factory()->create([
            'password'       => bcrypt('password'),
            'otp_code'       => '123456',
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        $this->postJson($this->verifyOtpEndpoint, [
            'email' => $user->email,
            'otp'   => '999999',
        ])->assertStatus(400)
          ->assertJsonFragment(['message' => 'Código inválido.']);
    }

    public function test_verify_otp_expirado_retorna_400(): void
    {
        $user = AgencyUser::factory()->create([
            'password'       => bcrypt('password'),
            'otp_code'       => '123456',
            'otp_expires_at' => now()->subMinutes(1), // ya expiró
        ]);

        $this->postJson($this->verifyOtpEndpoint, [
            'email' => $user->email,
            'otp'   => '123456',
        ])->assertStatus(400)
          ->assertJsonFragment(['message' => 'El código ha expirado. Iniciá sesión nuevamente.']);

        // El OTP debe limpiarse
        $this->assertNull($user->fresh()->otp_code);
    }

    public function test_verify_otp_sin_otp_pendiente_retorna_400(): void
    {
        $user = AgencyUser::factory()->create(['password' => bcrypt('password')]);

        $this->postJson($this->verifyOtpEndpoint, [
            'email' => $user->email,
            'otp'   => '123456',
        ])->assertStatus(400)
          ->assertJsonFragment(['message' => 'Código inválido o expirado.']);
    }

    public function test_verify_otp_usuario_inactivo_retorna_400(): void
    {
        $user = AgencyUser::factory()->inactive()->create([
            'password'       => bcrypt('password'),
            'otp_code'       => '123456',
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        $this->postJson($this->verifyOtpEndpoint, [
            'email' => $user->email,
            'otp'   => '123456',
        ])->assertStatus(400)
          ->assertJsonFragment(['message' => 'Código inválido o expirado.']);
    }

    public function test_otp_no_se_reutiliza_despues_de_verificacion_exitosa(): void
    {
        $user = AgencyUser::factory()->create([
            'password'       => bcrypt('password'),
            'otp_code'       => '123456',
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        // Primera verificación — exitosa
        $this->postJson($this->verifyOtpEndpoint, [
            'email' => $user->email,
            'otp'   => '123456',
        ])->assertStatus(200);

        // Segunda verificación con el mismo OTP — debe fallar
        $this->postJson($this->verifyOtpEndpoint, [
            'email' => $user->email,
            'otp'   => '123456',
        ])->assertStatus(400);
    }

    // -------------------------------------------------------------------------
    // Seguridad adicional: usuario eliminado y OTP cruzado entre usuarios
    // -------------------------------------------------------------------------

    public function test_usuario_agencia_eliminado_no_puede_hacer_login(): void
    {
        $user = AgencyUser::factory()->create([
            'password' => bcrypt('password'),
        ]);

        // Soft-delete del usuario
        $user->delete();

        $this->postJson($this->loginEndpoint, [
            'email'    => $user->email,
            'password' => 'password',
        ])->assertStatus(400)
          ->assertJsonFragment(['message' => 'Email y/o clave no válidos.']);
    }

    public function test_usuario_a_no_puede_usar_otp_de_usuario_b(): void
    {
        $userA = AgencyUser::factory()->create(['password' => bcrypt('password')]);
        $userB = AgencyUser::factory()->create([
            'password'       => bcrypt('password'),
            'otp_code'       => '999999',
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        // Usuario A intenta verificar con el OTP del usuario B (usando el email de A)
        // El OTP de A no es 999999, por lo que debe fallar
        $this->postJson($this->verifyOtpEndpoint, [
            'email' => $userA->email,
            'otp'   => '999999',
        ])->assertStatus(400);

        // El OTP del usuario B sigue siendo válido para B
        $this->postJson($this->verifyOtpEndpoint, [
            'email' => $userB->email,
            'otp'   => '999999',
        ])->assertStatus(200);
    }
}
