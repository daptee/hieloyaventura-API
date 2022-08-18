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
        if (!Schema::hasTable('excurtion_characteristics')) {
            Schema::table('excurtion_characteristics', function (Blueprint $table) {
                $table->foreign('excurtion_id')->references('id')->on('excurtions')->onDelete('no action');
                $table->foreign('characteristic_id')->references('id')->on('characteristics')->onDelete('no action');
            });
        }

        if (!Schema::hasTable('characteristics_types_translables')) {
            Schema::create('characteristics_types_translables', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->tinyInteger('lenguage_id')->unsigned();
                $table->longText('name')->nullable();
                $table->longText('description')->nullable();
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
        Schema::table('excurtion_characteristics', function (Blueprint $table) {
            //
        });
    }
};
