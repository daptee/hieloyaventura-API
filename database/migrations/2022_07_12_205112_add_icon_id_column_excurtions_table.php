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
        Schema::table('excurtions', function (Blueprint $table) {
            $table->unsignedBigInteger('icon_id')->nullable();
            $table->foreign('icon_id')->references('id')->on('icons')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('excurtions', function (Blueprint $table) {
            //
        });
    }
};
