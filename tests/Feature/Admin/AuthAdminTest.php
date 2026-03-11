<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\UserType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests de autenticación para el panel de administración.
 *
 * Endpoint: POST /api/login/admin
 */
class AuthAdminTest extends TestCase
{
    use RefreshDatabase;

    private string $endpoint = '/api/login/admin';

    // -------------------------------------------------------------------------
    // Casos exitosos
    // -------------------------------------------------------------------------

    public function test_admin_puede_iniciar_sesion_con_credenciales_validas(): void
    {
        $user = User::factory()->create([
            'user_type_id' => UserType::ADMIN,
            'password'     => bcrypt('password'),
        ]);

        $response = $this->postJson($this->endpoint, [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['access_token', 'token_type', 'expires_in', 'data'])
                 ->assertJson(['token_type' => 'Bearer']);
    }

    // -------------------------------------------------------------------------
    // Casos de error
    // -------------------------------------------------------------------------

    public function test_login_falla_con_password_incorrecto(): void
    {
        $user = User::factory()->create([
            'user_type_id' => UserType::ADMIN,
            'password'     => bcrypt('password'),
        ]);

        $response = $this->postJson($this->endpoint, [
            'email'    => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(400)
                 ->assertJsonFragment(['message' => 'Email y/o clave no válidos.']);
    }

    public function test_login_falla_si_email_no_existe(): void
    {
        $response = $this->postJson($this->endpoint, [
            'email'    => 'noexiste@test.com',
            'password' => 'password',
        ]);

        $response->assertStatus(400)
                 ->assertJsonFragment(['message' => 'Email no existente o usuario no admin.']);
    }

    public function test_usuario_tipo_cliente_no_puede_usar_login_admin(): void
    {
        $user = User::factory()->create([
            'user_type_id' => UserType::CLIENTE,
            'password'     => bcrypt('password'),
        ]);

        $response = $this->postJson($this->endpoint, [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(400)
                 ->assertJsonFragment(['message' => 'Email no existente o usuario no admin.']);
    }

    public function test_login_falla_si_faltan_campos_requeridos(): void
    {
        $this->postJson($this->endpoint, ['email' => 'test@test.com'])
             ->assertStatus(422);

        $this->postJson($this->endpoint, ['password' => 'password'])
             ->assertStatus(422);
    }

    public function test_login_falla_si_email_no_tiene_formato_valido(): void
    {
        $this->postJson($this->endpoint, [
            'email'    => 'no-es-un-email',
            'password' => 'password',
        ])->assertStatus(422);
    }
}
