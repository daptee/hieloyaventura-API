<?php

use App\Models\lenguage;
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
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('lenguage_id')->unsigned();
            $table->longText('question')->nullable();
            $table->longText('answer')->nullable();
            $table->timestamps();

            $table->foreign('lenguage_id')->references('id')->on('lenguages')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('faqs');
    }
};
