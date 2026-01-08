<?php

namespace Database\Seeders;

use App\Models\AgencyModule;
use Illuminate\Database\Seeder;

class AgencyModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $modules = [
            ['name' => 'Usuarios'],
            ['name' => 'Reservas web'],
            ['name' => 'Configuraciones generales'],
            ['name' => 'Agencias'],
            ['name' => 'Excursiones'],
            ['name' => 'Reservas Agencias']
        ];

        foreach ($modules as $module) {
            AgencyModule::updateOrCreate(['name' => $module['name']], $module);
        }
    }
}
