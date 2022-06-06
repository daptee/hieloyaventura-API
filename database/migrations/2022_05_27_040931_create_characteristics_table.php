<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('characteristics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('icon_id')->nullable();
            $table->unsignedTinyInteger('characteristic_type_id')->nullable();
            $table->unsignedTinyInteger('order')->nullable();
            $table->timestamps();

            $table->foreign('icon_id')->references('id')->on('icons')->onDelete('no action');
            $table->foreign('characteristic_type_id')->references('id')->on('characteristic_types')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('characteristics');
    }
};
