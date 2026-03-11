<?php

namespace Tests\Feature\Web;

use App\Models\User;
use App\Models\UserType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests de autenticación para usuarios web (clientes).
 *
 * Endpoint: POST /api/login
 *
 * Nota: los usuarios web (tipo CLIENTE) se autentican con JWT pero NO tienen
 * acceso a endpoints de administración ni de agencias. El login web devuelve
 * el JWT directamente, sin flujo 2FA.
 */
class AuthWebTest extends TestCase
{
    use RefreshDatabase;

    private string $endpoint = '/api/login';

    // -------------------------------------------------------------------------
    // Casos exitosos
    // -------------------------------------------------------------------------

    public function test_usuario_web_puede_iniciar_sesion_con_credenciales_validas(): void
    {
        $user = User::factory()->create([
            'user_type_id' => UserType::CLIENTE,
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

    public function test_admin_tambien_puede_usar_endpoint_login_web(): void
    {
        // El endpoint /login no discrimina por tipo, a diferencia de /login/admin
        $user = User::factory()->create([
            'user_type_id' => UserType::ADMIN,
            'password'     => bcrypt('password'),
        ]);

        $this->postJson($this->endpoint, [
            'email'    => $user->email,
            'password' => 'password',
        ])->assertStatus(200)
          ->assertJsonStructure(['access_token']);
    }

    // -------------------------------------------------------------------------
    // Casos de error
    // -------------------------------------------------------------------------

    public function test_login_falla_con_password_incorrecto(): void
    {
        $user = User::factory()->create([
            'user_type_id' => UserType::CLIENTE,
            'password'     => bcrypt('password'),
        ]);

        $this->postJson($this->endpoint, [
            'email'    => $user->email,
            'password' => 'wrong-password',
        ])->assertStatus(400)
          ->assertJsonFragment(['message' => 'Usuario y/o clave no válidos.']);
    }

    public function test_login_falla_si_email_no_existe(): void
    {
        $this->postJson($this->endpoint, [
            'email'    => 'noexiste@test.com',
            'password' => 'password',
        ])->assertStatus(400)
          ->assertJsonFragment(['message' => 'Usuario y/o clave no válidos.']);
    }

    public function test_login_falla_si_faltan_campos(): void
    {
        // El login devuelve 400 cuando faltan campos (validación manual en el controlador)
        $this->postJson($this->endpoint, ['email' => 'test@test.com'])
             ->assertStatus(400);

        $this->postJson($this->endpoint, ['password' => 'password'])
             ->assertStatus(400);
    }

    // -------------------------------------------------------------------------
    // Aislamiento: el token web no da acceso a endpoints de admin o agencias
    // -------------------------------------------------------------------------

    public function test_token_web_no_da_acceso_a_endpoints_admin(): void
    {
        $user = User::factory()->create([
            'user_type_id' => UserType::CLIENTE,
            'password'     => bcrypt('password'),
        ]);

        $loginResponse = $this->postJson($this->endpoint, [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $token = $loginResponse->json('access_token');

        // El endpoint /agencies/{code} requiere módulo AGENCIAS (admin). El usuario CLIENTE
        // pasa jwt.verify (tiene JWT válido) pero falla el check de módulo → 403
        $this->getJson('/api/agencies/NONEXISTENT', ['Authorization' => 'Bearer ' . $token])
             ->assertStatus(403);
    }

    public function test_token_web_no_da_acceso_a_endpoints_de_agencia(): void
    {
        $user = User::factory()->create([
            'user_type_id' => UserType::CLIENTE,
            'password'     => bcrypt('password'),
        ]);

        $loginResponse = $this->postJson($this->endpoint, [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $token = $loginResponse->json('access_token');

        // El endpoint /agency/users usa jwt.admin_or_agency: el usuario CLIENTE pasa
        // la autenticación (JWT válido en tabla users) pero no tiene módulo AGENCIAS → 403
        $this->getJson('/api/agency/users', ['Authorization' => 'Bearer ' . $token])
             ->assertStatus(403);
    }
}
