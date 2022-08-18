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
        Schema::create('paxes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_reservation_id')->nullable();
            $table->unsignedBigInteger('pax_type_id')->nullable();
            $table->unsignedBigInteger('nationality_id')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('name', 100)->nullable();
            $table->string('dni', 100)->nullable();
            $table->timestamps();

            $table->foreign('user_reservation_id')->references('id')->on('user_reservations')->onDelete('no action');
            $table->foreign('pax_type_id')->references('id')->on('pax_types')->onDelete('no action');
            $table->foreign('nationality_id')->references('id')->on('nationalities')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('paxes');
    }
};
