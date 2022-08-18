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
        if (!Schema::hasTable('user_reservations')) {
            Schema::create('user_reservations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->unsignedBigInteger('hotel_id')->nullable();
                $table->unsignedBigInteger('excurtion_id')->nullable();
                $table->unsignedBigInteger('reservation_status_id')->nullable();
                $table->unsignedBigInteger('turn_id')->nullable();
                $table->string('hotel_name', 255)->nullable();
                $table->decimal('price')->nullable();
                $table->decimal('children_price')->nullable();
                $table->decimal('special_discount')->nullable();
                $table->tinyInteger('is_paid')->nullable();
                $table->tinyInteger('is_transfer')->nullable();

                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('no action');
                $table->foreign('hotel_id')->references('id')->on('hotels')->onDelete('no action');
                $table->foreign('turn_id')->references('id')->on('turns')->onDelete('no action');
                $table->foreign('excurtion_id')->references('id')->on('excurtions')->onDelete('no action');
                $table->foreign('reservation_status_id')->references('id')->on('reservation_statuses')->onDelete('no action');
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
        Schema::dropIfExists('user_reservations');
    }
};
