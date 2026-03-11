<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('agency_modules')) {
            Schema::create('agency_modules', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('agency_modules');
    }
};
