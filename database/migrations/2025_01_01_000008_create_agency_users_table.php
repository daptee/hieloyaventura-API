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
                $table->id();
                $table->unsignedBigInteger('agency_user_type_id')->default(1);
                $table->string('user')->nullable();
                $table->string('name');
                $table->string('last_name');
                $table->string('email')->unique();
                $table->string('password');
                $table->string('agency_code');
                $table->boolean('active')->default(1);
                $table->boolean('can_view_all_sales')->default(0);
                $table->timestamp('terms_and_conditions')->nullable();
                $table->string('otp_code', 6)->nullable();
                $table->timestamp('otp_expires_at')->nullable();
                $table->string('pending_email')->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('agency_users');
    }
};
