<?php

use App\Models\ReservationStatus;
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
        Schema::table('reservation_statuses', function (Blueprint $table) {
            $table->string('name', 100);
        });

        ReservationStatus::create([
            'name' => 'RECHAZADA',
        ]);
        ReservationStatus::create([
            'name' => 'INICIADA',
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
