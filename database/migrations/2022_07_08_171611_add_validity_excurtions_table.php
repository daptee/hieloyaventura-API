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
        if (!Schema::hasColumn('excurtions', 'validity_from')) {
            Schema::table('excurtions', function (Blueprint $table) {
                $table->dateTime('validity_from')->nullable();
            });
        }
        if (!Schema::hasColumn('excurtions', 'validity_to')) {
            Schema::table('excurtions', function (Blueprint $table) {
                $table->dateTime('validity_to')->nullable();
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
        Schema::table('excurtions', function (Blueprint $table) {
            //
        });
    }
};
