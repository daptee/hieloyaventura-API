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
        Schema::create('excurtion_characteristics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('excurtion_id')->nullable();
            $table->unsignedBigInteger('characteristic_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('excurtion_characteristics');
    }
};
