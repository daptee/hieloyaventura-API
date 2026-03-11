<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('agency_user_modules')) {
            Schema::create('agency_user_modules', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('agency_user_id');
                $table->unsignedBigInteger('agency_module_id');
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('agency_user_modules');
    }
};
