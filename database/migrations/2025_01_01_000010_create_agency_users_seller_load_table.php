<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('agency_users_seller_load')) {
            Schema::create('agency_users_seller_load', function (Blueprint $table) {
                $table->integer('id')->autoIncrement();
                $table->integer('id_user')->nullable();
                $table->integer('agency_code')->nullable();
                $table->integer('seller_load')->default(5);
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('agency_users_seller_load');
    }
};
