<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('agency_users')) {
            Schema::create('agency_users', function (Blueprint $table) {
                $table->integer('id')->autoIncrement();
                $table->integer('agency_user_type_id')->nullable();
                $table->string('user')->nullable();
                $table->string('password')->nullable();
                $table->boolean('password_expired')->default(false);
                $table->string('name')->nullable();
                $table->string('last_name')->nullable();
                $table->string('email')->nullable();
                $table->string('otp_code', 6)->nullable();
                $table->timestamp('otp_expires_at')->nullable();
                $table->string('pending_email')->nullable();
                $table->string('agency_code', 50)->nullable();
                $table->boolean('active')->default(1);
                $table->boolean('can_view_all_sales')->default(0)->notNull();
                $table->dateTime('terms_and_conditions')->nullable();  // datetime, no timestamp
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('agency_users');
    }
};
