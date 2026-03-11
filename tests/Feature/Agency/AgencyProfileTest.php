<?php

namespace Tests\Feature\Agency;

use App\Mail\AgencyOtpMailable;
use App\Models\AgencyUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Feature\Concerns\CreatesUsers;
use Tests\TestCase;

/**
 * Tests de edición del perfil propio de un usuario de agencia.
 *
 * PUT  /api/agency/users/profile              → actualizar datos (sin cambio de email)
 * PUT  /api/agency/users/profile              → cambio de email → envía OTP al correo actual
 * POST /api/agency/users/profile/confirm-email-change → confirmar cambio con OTP
 */
class AgencyProfileTest extends TestCase
{
    use RefreshDatabase, CreatesUsers;

    private string $profileEndpoint      = '/api/agency/users/profile';
    private string $confirmEmailEndpoint = '/api/agency/users/profile/confirm-email-change';

    // -------------------------------------------------------------------------
    // Actualización de datos sin cambio de email
    // -------------------------------------------------------------------------

    public function test_usuario_agencia_puede_actualizar_su_nombre(): void
    {
        ['user' => $user, 'token' => $token] = $this->createAgencyUser('AG001');

        $response = $this->putJson($this->profileEndpoint, [
            'name'      => 'Nuevo Nombre',
            'last_name' => 'Nuevo Apellido',
            'email'     => $user->email, // mismo email → no dispara OTP
        ], $this->authHeaders($token));

        $response->assertStatus(200)
                 ->assertJsonPath('user.name', 'Nuevo Nombre')
                 ->assertJsonPath('user.last_name', 'Nuevo Apellido');

        $this->assertDatabaseHas('agency_users', [
            'id'        => $user->id,
            'name'      => 'Nuevo Nombre',
            'last_name' => 'Nuevo Apellido',
        ]);
    }

    public function test_actualizacion_sin_token_retorna_401(): void
    {
        $this->putJson($this->profileEndpoint, [
            'name'      => 'Test',
            'last_name' => 'Test',
            'email'     => 'test@test.com',
        ])->assertStatus(401);
    }

    public function test_actualizacion_falla_si_faltan_campos_requeridos(): void
    {
        ['token' => $token] = $this->createAgencyUser('AG001');

        $this->putJson($this->profileEndpoint, [
            'email' => 'test@test.com',
        ], $this->authHeaders($token))
             ->assertStatus(422);
    }

    // -------------------------------------------------------------------------
    // Cambio de email → flujo con OTP
    // -------------------------------------------------------------------------

    public function test_cambio_de_email_envia_otp_y_retorna_pending_email_change(): void
    {
        Mail::fake();

        ['user' => $user, 'token' => $token] = $this->createAgencyUser('AG001');

        $response = $this->putJson($this->profileEndpoint, [
            'name'      => $user->name,
            'last_name' => $user->last_name,
            'email'     => 'nuevo@email.com',
        ], $this->authHeaders($token));

        $response->assertStatus(200)
                 ->assertJson(['pending_email_change' => true])
                 ->assertJsonMissing(['user']); // no actualiza todavía

        Mail::assertSent(AgencyOtpMailable::class, fn ($mail) =>
            $mail->hasTo($user->email) && $mail->type === 'email_change'
        );

        $this->assertEquals('nuevo@email.com', $user->fresh()->pending_email);
        $this->assertEquals($user->email, $user->fresh()->email); // no cambió aún
    }

    public function test_cambio_de_email_a_uno_ya_en_uso_retorna_422(): void
    {
        ['user' => $user, 'token' => $token] = $this->createAgencyUser('AG001');
        $otherUser = AgencyUser::factory()->create();

        $this->putJson($this->profileEndpoint, [
            'name'      => $user->name,
            'last_name' => $user->last_name,
            'email'     => $otherUser->email,
        ], $this->authHeaders($token))
             ->assertStatus(422);
    }

    // -------------------------------------------------------------------------
    // Confirmación del cambio de email con OTP
    // -------------------------------------------------------------------------

    public function test_confirmar_email_con_otp_correcto_actualiza_el_email(): void
    {
        $user = AgencyUser::factory()->forAgency('AG001')->create([
            'otp_code'       => '654321',
            'otp_expires_at' => now()->addMinutes(10),
            'pending_email'  => 'nuevo@email.com',
        ]);

        ['token' => $token] = $this->createAgencyUser('AG001');
        // Usamos el token del user con OTP pendiente
        $token = \Auth::guard('agency')->login($user);

        $response = $this->postJson($this->confirmEmailEndpoint, [
            'otp' => '654321',
        ], $this->authHeaders($token));

        $response->assertStatus(200)
                 ->assertJsonPath('user.email', 'nuevo@email.com');

        $this->assertDatabaseHas('agency_users', [
            'id'    => $user->id,
            'email' => 'nuevo@email.com',
        ]);

        $this->assertNull($user->fresh()->otp_code);
        $this->assertNull($user->fresh()->pending_email);
    }

    public function test_confirmar_email_con_otp_incorrecto_retorna_400(): void
    {
        $user = AgencyUser::factory()->forAgency('AG001')->create([
            'otp_code'       => '654321',
            'otp_expires_at' => now()->addMinutes(10),
            'pending_email'  => 'nuevo@email.com',
        ]);
        $token = \Auth::guard('agency')->login($user);

        $this->postJson($this->confirmEmailEndpoint, [
            'otp' => '000000',
        ], $this->authHeaders($token))
             ->assertStatus(400)
             ->assertJsonFragment(['message' => 'Código inválido.']);

        // El email no debe haber cambiado
        $this->assertNotEquals('nuevo@email.com', $user->fresh()->email);
    }

    public function test_confirmar_email_con_otp_expirado_retorna_400(): void
    {
        $user = AgencyUser::factory()->forAgency('AG001')->create([
            'otp_code'       => '654321',
            'otp_expires_at' => now()->subMinutes(1),
            'pending_email'  => 'nuevo@email.com',
        ]);
        $token = \Auth::guard('agency')->login($user);

        $this->postJson($this->confirmEmailEndpoint, [
            'otp' => '654321',
        ], $this->authHeaders($token))
             ->assertStatus(400)
             ->assertJsonFragment(['message' => 'El código ha expirado. Intentá editar el email nuevamente.']);
    }

    public function test_confirmar_email_sin_cambio_pendiente_retorna_400(): void
    {
        ['user' => $user, 'token' => $token] = $this->createAgencyUser('AG001');

        $this->postJson($this->confirmEmailEndpoint, [
            'otp' => '123456',
        ], $this->authHeaders($token))
             ->assertStatus(400)
             ->assertJsonFragment(['message' => 'No hay un cambio de email pendiente.']);
    }
}
