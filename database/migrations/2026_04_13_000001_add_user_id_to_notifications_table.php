<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up()
  {
    Schema::table('notifications', function (Blueprint $table) {
      if (!Schema::hasColumn('notifications', 'user_id')) {
        $table->unsignedBigInteger('user_id')->nullable()->after('id');
        $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
      }
    });
  }

  public function down()
  {
    Schema::table('notifications', function (Blueprint $table) {
      if (Schema::hasColumn('notifications', 'user_id')) {
        $table->dropForeign(['user_id']);
        $table->dropColumn('user_id');
      }
    });
  }
};
