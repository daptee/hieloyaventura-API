<?php

use App\Models\Lenguage;
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
        Schema::create('lenguages', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->string('name', 30);
            $table->string('abreviation', 10);
        });
        Lenguage::create([
            'abreviation' => 'spa',
            'name' => 'Español',
        ]);
        Lenguage::create([
            'name' => 'Ingles',
            'abreviation' => 'es',
        ]);
        Lenguage::create([
            'name' => 'Portugues',
            'abreviation' => 'por',
        ]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lenguages');
    }
};
