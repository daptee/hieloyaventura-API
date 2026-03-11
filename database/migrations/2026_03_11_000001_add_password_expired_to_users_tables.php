<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'password_expired')) {
                $table->boolean('password_expired')->default(false)->after('password');
            }
        });

        Schema::table('agency_users', function (Blueprint $table) {
            if (!Schema::hasColumn('agency_users', 'password_expired')) {
                $table->boolean('password_expired')->default(false)->after('password');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'password_expired')) {
                $table->dropColumn('password_expired');
            }
        });

        Schema::table('agency_users', function (Blueprint $table) {
            if (Schema::hasColumn('agency_users', 'password_expired')) {
                $table->dropColumn('password_expired');
            }
        });
    }
};
