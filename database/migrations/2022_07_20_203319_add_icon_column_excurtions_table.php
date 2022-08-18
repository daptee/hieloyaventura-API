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
        if (!Schema::hasColumn('excurtions', 'icon')) {
            Schema::table('excurtions', function (Blueprint $table) {
                $table->dropForeign('excurtions_icon_id_foreign');
                $table->dropColumn('icon_id');
                $table->string('icon', 20)->nullable();
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
