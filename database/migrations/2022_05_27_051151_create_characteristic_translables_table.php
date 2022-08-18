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
        if (!Schema::hasTable('characteristic_translables')) {
            Schema::create('characteristic_translables', function (Blueprint $table) {
                $table->id();
                $table->unsignedTinyInteger('lenguage_id')->nullable();
                $table->unsignedBigInteger('characteristic_id')->nullable();
                $table->string('name', 255)->nullable();
                $table->json('description')->nullable();
                $table->timestamps();

                $table->foreign('lenguage_id')->references('id')->on('lenguages')->onDelete('no action');
                $table->foreign('characteristic_id')->references('id')->on('characteristics')->onDelete('no action');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('characteristic_translables');
    }
};
