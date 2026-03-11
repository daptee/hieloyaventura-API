<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('user_modules')) {
            Schema::create('user_modules', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('module_id');
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('user_modules');
    }
};
