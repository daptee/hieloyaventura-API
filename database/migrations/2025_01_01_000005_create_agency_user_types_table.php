<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('agency_user_types')) {
            Schema::create('agency_user_types', function (Blueprint $table) {
                $table->integer('id')->autoIncrement();
                $table->string('name')->nullable();
                $table->timestamps();
            });

            DB::table('agency_user_types')->insert([
                ['id' => 1, 'name' => 'Admin',      'created_at' => now(), 'updated_at' => now()],
                ['id' => 2, 'name' => 'Vendedor',   'created_at' => now(), 'updated_at' => now()],
                ['id' => 3, 'name' => 'Comercial',  'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('agency_user_types');
    }
};
