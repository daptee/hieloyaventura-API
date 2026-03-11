<?php

namespace Tests\Feature\Agency;

use App\Models\AgencyUser;
use App\Models\Module;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesUsers;
use Tests\TestCase;

/**
 * Tests de aislamiento entre agencias (cross-agency isolation).
 *
 * Verifica que un usuario de la agencia A no puede ver ni manipular
 * los datos de la agencia B, aunque tenga un JWT válido.
 *
 * También verifica que los mismos endpoints son accesibles para admins
 * con el módulo AGENCIAS (dual-auth endpoints).
 */
class AgencyIsolationTest extends TestCase
{
    use RefreshDatabase, CreatesUsers;

    // -------------------------------------------------------------------------
    // GET /api/agency/users — listar usuarios de la agencia
    // -------------------------------------------------------------------------

    public function test_agencia_solo_ve_sus_propios_usuarios(): void
    {
        // Crear usuarios de dos agencias distintas
        $agencyA_user  = AgencyUser::factory()->forAgency('AGENCIA_A')->create();
        $agencyA_user2 = AgencyUser::factory()->forAgency('AGENCIA_A')->create();
        $agencyB_user  = AgencyUser::factory()->forAgency('AGENCIA_B')->create();

        $token = \Auth::guard('agency')->login($agencyA_user);

        $response = $this->getJson('/api/agency/users', $this->authHeaders($token));

        $response->assertStatus(200);

        $ids = collect($response->json())->pluck('id')->toArray();

        $this->assertContains($agencyA_user->id,  $ids, 'Debe ver usuarios de su propia agencia');
        $this->assertContains($agencyA_user2->id, $ids, 'Debe ver usuarios de su propia agencia');
        $this->assertNotContains($agencyB_user->id, $ids, 'No debe ver usuarios de otra agencia');
    }

    public function test_admin_con_modulo_agencias_ve_todos_los_usuarios(): void
    {
        AgencyUser::factory()->forAgency('AGENCIA_A')->create();
        AgencyUser::factory()->forAgency('AGENCIA_B')->create();

        ['token' => $token] = $this->createAdminWithModules([Module::AGENCIAS]);

        $response = $this->getJson('/api/agency/users', $this->authHeaders($token));

        $response->assertStatus(200);

        // El admin ve al menos 2 usuarios (los creados arriba)
        $this->assertGreaterThanOrEqual(2, count($response->json()));
    }

    // -------------------------------------------------------------------------
    // GET /api/agency/users/seller/{agency_code}
    // -------------------------------------------------------------------------

    public function test_agencia_A_no_puede_ver_vendedores_de_agencia_B(): void
    {
        $agencyA_user = AgencyUser::factory()->forAgency('AGENCIA_A')->create();
        AgencyUser::factory()->forAgency('AGENCIA_B')->vendedor()->create();

        $token = \Auth::guard('agency')->login($agencyA_user);

        // El endpoint fuerza el agency_code del token, ignora el parámetro de URL
        $response = $this->getJson('/api/agency/users/seller/AGENCIA_B', $this->authHeaders($token));

        $response->assertStatus(200);

        // Todos los resultados deben ser de AGENCIA_A, no de AGENCIA_B
        $codes = collect($response->json())->pluck('agency_code')->unique()->toArray();
        $this->assertNotContains('AGENCIA_B', $codes, 'No debe ver vendedores de otra agencia');
    }

    public function test_admin_con_modulo_agencias_ve_vendedores_de_cualquier_agencia(): void
    {
        AgencyUser::factory()->forAgency('AGENCIA_B')->vendedor()->create();

        ['token' => $token] = $this->createAdminWithModules([Module::AGENCIAS]);

        $response = $this->getJson('/api/agency/users/seller/AGENCIA_B', $this->authHeaders($token));

        $response->assertStatus(200);
    }

    // -------------------------------------------------------------------------
    // Acceso a endpoints protegidos sin token
    // -------------------------------------------------------------------------

    public function test_endpoint_de_agencia_sin_token_retorna_401(): void
    {
        $this->getJson('/api/agency/users')
             ->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // Token de agencia no da acceso a endpoints exclusivamente admin
    // -------------------------------------------------------------------------

    public function test_token_de_agencia_no_da_acceso_a_gestion_de_agencias(): void
    {
        // GET /api/agencies requiere jwt.verify (admin), no jwt.agency
        $agencyUser = AgencyUser::factory()->forAgency('AGENCIA_A')->create();
        $token      = \Auth::guard('agency')->login($agencyUser);

        // Un token de agencia NO puede pasar el middleware jwt.verify (admin)
        $this->getJson('/api/agencies', $this->authHeaders($token))
             ->assertStatus(401);
    }
}
