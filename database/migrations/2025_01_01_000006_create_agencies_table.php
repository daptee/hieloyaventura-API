<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('agencies')) {
            Schema::create('agencies', function (Blueprint $table) {
                $table->id();
                $table->string('agency_code')->unique();
                $table->string('api_key')->unique()->nullable();
                $table->string('name')->nullable();
                $table->json('configurations')->nullable();
                $table->string('email_integration_notification')->nullable();
                $table->boolean('active')->default(1);
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('agencies');
    }
};
