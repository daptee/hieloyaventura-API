<?php

use App\Models\ReservationStatus;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        ReservationStatus::where('name', 'INICIADA')->update(['name' => 'STARTED']);
        ReservationStatus::where('name', 'RECHAZADA')->update(['name' => 'REJECTED']);
        ReservationStatus::create(['name' => 'COMPLETED']);
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
