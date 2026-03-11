<?php

namespace Tests\Feature\Admin;

use App\Models\Agency;
use App\Models\Module;
use App\Models\User;
use App\Models\UserModule;
use App\Models\UserType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesUsers;
use Tests\TestCase;

/**
 * Tests de control de acceso basado en módulos para usuarios admin.
 *
 * Verifica que:
 * - Un admin SIN el módulo recibe 403.
 * - Un admin CON el módulo recibe la respuesta esperada.
 * - Un usuario no-admin es rechazado aunque tenga token válido.
 */
class ModulePermissionsTest extends TestCase
{
    use RefreshDatabase, CreatesUsers;

    // -------------------------------------------------------------------------
    // Módulo AGENCIAS — GET /api/agencies
    // -------------------------------------------------------------------------

    public function test_admin_con_modulo_agencias_puede_listar_agencias(): void
    {
        ['token' => $token] = $this->createAdminWithModules([Module::AGENCIAS]);

        $this->getJson('/api/agencies', $this->authHeaders($token))
             ->assertStatus(200);
    }

    public function test_admin_sin_modulo_agencias_recibe_403(): void
    {
        ['token' => $token] = $this->createAdminWithModules([]);  // sin módulos

        $this->getJson('/api/agencies', $this->authHeaders($token))
             ->assertStatus(403)
             ->assertJsonFragment(['message' => 'No tiene permisos para realizar esta acción.']);
    }

    public function test_request_sin_token_recibe_401(): void
    {
        $this->getJson('/api/agencies')
             ->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // Módulo AGENCIAS — GET /api/agencies/{code} devuelve api_key
    // -------------------------------------------------------------------------

    public function test_admin_con_modulo_agencias_recibe_api_key_en_show(): void
    {
        ['token' => $token] = $this->createAdminWithModules([Module::AGENCIAS]);

        $agency = Agency::create([
            'agency_code' => 'TST001',
            'api_key'     => 'secret-api-key-12345',
            'name'        => 'Agencia Test',
        ]);

        $response = $this->getJson('/api/agencies/' . $agency->agency_code, $this->authHeaders($token));

        $response->assertStatus(200)
                 ->assertJsonFragment(['api_key' => 'secret-api-key-12345']);
    }

    public function test_admin_sin_modulo_agencias_no_puede_ver_agencia(): void
    {
        ['token' => $token] = $this->createAdminWithModules([Module::RESERVAS_WEB]);

        Agency::create(['agency_code' => 'TST002', 'api_key' => 'key', 'name' => 'Test']);

        $this->getJson('/api/agencies/TST002', $this->authHeaders($token))
             ->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // Módulo USUARIOS — GET /api/users
    // -------------------------------------------------------------------------

    public function test_admin_con_modulo_usuarios_puede_listar_usuarios(): void
    {
        ['token' => $token] = $this->createAdminWithModules([Module::USUARIOS]);

        $this->getJson('/api/users', $this->authHeaders($token))
             ->assertStatus(200);
    }

    public function test_admin_sin_modulo_usuarios_recibe_403_en_users(): void
    {
        ['token' => $token] = $this->createAdminWithModules([Module::AGENCIAS]);

        $this->getJson('/api/users', $this->authHeaders($token))
             ->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // Usuario no-admin con token válido (tipo CLIENTE o VENDEDOR)
    // -------------------------------------------------------------------------

    public function test_usuario_cliente_con_token_recibe_403_en_endpoint_admin(): void
    {
        ['token' => $token] = $this->createWebUser();

        $this->getJson('/api/agencies', $this->authHeaders($token))
             ->assertStatus(403);
    }
}
