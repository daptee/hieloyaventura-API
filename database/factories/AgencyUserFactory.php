<?php

namespace Database\Factories;

use App\Models\AgencyUser;
use App\Models\AgencyUserType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<AgencyUser>
 */
class AgencyUserFactory extends Factory
{
    protected $model = AgencyUser::class;

    public function definition()
    {
        return [
            'agency_user_type_id' => AgencyUserType::ADMIN,
            'user'                => $this->faker->userName(),
            'name'                => $this->faker->firstName(),
            'last_name'           => $this->faker->lastName(),
            'email'               => $this->faker->unique()->safeEmail(),
            'password'            => Hash::make('password'),
            'agency_code'         => strtoupper($this->faker->bothify('AG###')),
            'active'              => 1,
            'can_view_all_sales'  => 0,
        ];
    }

    /** Usuario de agencia inactivo */
    public function inactive(): static
    {
        return $this->state(['active' => 0]);
    }

    /** Usuario tipo Vendedor */
    public function vendedor(): static
    {
        return $this->state(['agency_user_type_id' => AgencyUserType::VENDEDOR]);
    }

    /** Forzar un agency_code específico */
    public function forAgency(string $agencyCode): static
    {
        return $this->state(['agency_code' => $agencyCode]);
    }
}
