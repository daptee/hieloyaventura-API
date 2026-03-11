<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('agencies')) {
            Schema::create('agencies', function (Blueprint $table) {
                $table->integer('id')->autoIncrement();
                $table->string('agency_code', 100)->nullable();
                $table->string('api_key')->nullable();
                $table->text('configurations')->nullable();
                $table->string('email_integration_notification')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('agencies');
    }
};
