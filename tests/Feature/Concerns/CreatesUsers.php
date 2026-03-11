<?php

namespace Tests\Feature\Concerns;

use App\Models\AgencyUser;
use App\Models\Module;
use App\Models\User;
use App\Models\UserModule;
use App\Models\UserType;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Helpers para crear usuarios y tokens JWT en tests de feature.
 * Usar en clases que extiendan TestCase y usen RefreshDatabase.
 */
trait CreatesUsers
{
    /**
     * Crea un usuario admin con los módulos indicados y devuelve su JWT.
     */
    protected function createAdminWithModules(array $moduleIds = []): array
    {
        $user = User::factory()->create([
            'user_type_id' => UserType::ADMIN,
        ]);

        foreach ($moduleIds as $moduleId) {
            UserModule::create([
                'user_id'   => $user->id,
                'module_id' => $moduleId,
            ]);
        }

        $token = JWTAuth::fromUser($user);

        return ['user' => $user, 'token' => $token];
    }

    /**
     * Crea un usuario web (CLIENTE) y devuelve su JWT.
     */
    protected function createWebUser(): array
    {
        $user  = User::factory()->create(['user_type_id' => UserType::CLIENTE]);
        $token = JWTAuth::fromUser($user);

        return ['user' => $user, 'token' => $token];
    }

    /**
     * Crea un usuario de agencia con el agency_code indicado y devuelve su JWT.
     */
    protected function createAgencyUser(string $agencyCode, array $overrides = []): array
    {
        $user  = AgencyUser::factory()->forAgency($agencyCode)->create($overrides);
        $token = Auth::guard('agency')->login($user);

        return ['user' => $user, 'token' => $token];
    }

    /**
     * Devuelve los headers de autorización para incluir en el request.
     */
    protected function authHeaders(string $token): array
    {
        return ['Authorization' => 'Bearer ' . $token];
    }
}
