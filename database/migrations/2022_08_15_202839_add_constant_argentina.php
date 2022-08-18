<?php

use App\Models\Nationality;
use App\Models\PaxType;
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
        PaxType::create([
            'name' => 'Adulto'
        ]);
        PaxType::create([
            'name' => 'Niño'
        ]);
        Nationality::create([
            'name' => 'Argentina'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
