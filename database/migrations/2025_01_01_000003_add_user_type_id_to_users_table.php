<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Agrega las columnas que tiene la tabla users real pero que no están en la migración base de Laravel.
// Todas son nullable para no romper el UserFactory existente.
return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('users')) return;

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'user_type_id')) {
                $table->integer('user_type_id')->default(1)->after('id');
            }
            if (!Schema::hasColumn('users', 'lenguage_id')) {
                $table->unsignedTinyInteger('lenguage_id')->nullable();
            }
            if (!Schema::hasColumn('users', 'nationality_id')) {
                $table->unsignedBigInteger('nationality_id')->nullable();
            }
            if (!Schema::hasColumn('users', 'birth_date')) {
                $table->date('birth_date')->nullable();
            }
            if (!Schema::hasColumn('users', 'dni')) {
                $table->string('dni', 100)->nullable();
            }
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 100)->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['user_type_id', 'lenguage_id', 'nationality_id', 'birth_date', 'dni', 'phone'] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
                        $table->dropColumn($col);
                    }
                }
            }
        });
    }
};
