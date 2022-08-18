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
        if (!Schema::hasTable('excurtions')) {
            Schema::create('excurtions', function (Blueprint $table) {
                $table->id();
                $table->string('link_map', 255)->nullable();
                $table->string('code_excurtion', 255)->nullable();
                $table->decimal('price', 10, 2)->nullable();
                $table->decimal('price_children', 10, 2)->nullable();
                $table->decimal('price_special', 10, 2)->nullable();
                $table->unsignedTinyInteger('is_transfer')->nullable();
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
        Schema::dropIfExists('excurtions');
    }
};
