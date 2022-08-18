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
        if (!Schema::hasTable('picture_excurtions')) {
            Schema::create('picture_excurtions', function (Blueprint $table) {
                $table->id();
                $table->string('link', 255);
                $table->unsignedTinyInteger('order')->nullable();
                $table->unsignedBigInteger('excurtion_id')->nullable();

                $table->foreign('excurtion_id')->references('id')->on('excurtions')->onDelete('no action');
                $table->timestamps();
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
        Schema::dropIfExists('picture_excurtions');
    }
};
