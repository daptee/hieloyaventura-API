<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('modules')) {
            Schema::create('modules', function (Blueprint $table) {
                $table->integer('id')->autoIncrement();
                $table->string('name')->nullable();
                $table->timestamps();
            });

            DB::table('modules')->insert([
                ['id' => 1, 'name' => 'Usuarios',           'created_at' => now(), 'updated_at' => now()],
                ['id' => 2, 'name' => 'Reservas Web',       'created_at' => now(), 'updated_at' => now()],
                ['id' => 3, 'name' => 'Configuraciones',    'created_at' => now(), 'updated_at' => now()],
                ['id' => 4, 'name' => 'Agencias',           'created_at' => now(), 'updated_at' => now()],
                ['id' => 5, 'name' => 'Excursiones',        'created_at' => now(), 'updated_at' => now()],
                ['id' => 6, 'name' => 'Reservas Agencias',  'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('modules');
    }
};
