<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'user_type_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('user_type_id')->default(1)->after('id');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('users', 'user_type_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('user_type_id');
            });
        }
    }
};
