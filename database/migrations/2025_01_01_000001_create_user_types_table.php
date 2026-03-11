<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('user_types')) {
            Schema::create('user_types', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->timestamps();
            });

            DB::table('user_types')->insert([
                ['id' => 1, 'name' => 'Cliente',   'created_at' => now(), 'updated_at' => now()],
                ['id' => 2, 'name' => 'Admin',     'created_at' => now(), 'updated_at' => now()],
                ['id' => 3, 'name' => 'Vendedor',  'created_at' => now(), 'updated_at' => now()],
                ['id' => 4, 'name' => 'Editor',    'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('user_types');
    }
};
