<?php

use App\Models\UserReservation;
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
        Schema::create('reservation_paxes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pax_type_id');
            $table->foreignIdFor(UserReservation::class, 'user_reservation_id');
            $table->decimal('price');
            $table->integer('quantity')->unsigned()->default(1);
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
        Schema::dropIfExists('reservation_paxes');
    }
};
