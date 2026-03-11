<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Esta migración agrega las columnas OTP a la tabla agency_users en la DB real (producción/dev).
// En SQLite (tests), la tabla ya incluye estas columnas desde 2025_01_01_000008_create_agency_users_table.php,
// por lo que los guards hasColumn evitan errores de duplicado.
return new class extends Migration
{
    public function up()
    {
        Schema::table('agency_users', function (Blueprint $table) {
            if (!Schema::hasColumn('agency_users', 'otp_code')) {
                $table->string('otp_code', 6)->nullable()->after('email');
            }
            if (!Schema::hasColumn('agency_users', 'otp_expires_at')) {
                $table->timestamp('otp_expires_at')->nullable()->after('otp_code');
            }
            if (!Schema::hasColumn('agency_users', 'pending_email')) {
                $table->string('pending_email')->nullable()->after('otp_expires_at');
            }
        });
    }

    public function down()
    {
        Schema::table('agency_users', function (Blueprint $table) {
            $columns = array_filter(
                ['otp_code', 'otp_expires_at', 'pending_email'],
                fn($col) => Schema::hasColumn('agency_users', $col)
            );
            if ($columns) {
                $table->dropColumn(array_values($columns));
            }
        });
    }
};
