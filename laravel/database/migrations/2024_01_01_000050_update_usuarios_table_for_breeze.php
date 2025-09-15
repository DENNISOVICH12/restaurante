<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            if (!Schema::hasColumn('usuarios', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable()->after('password');
            }

            if (!Schema::hasColumn('usuarios', 'remember_token')) {
                $table->rememberToken()->after('email_verified_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            if (Schema::hasColumn('usuarios', 'remember_token')) {
                $table->dropColumn('remember_token');
            }

            if (Schema::hasColumn('usuarios', 'email_verified_at')) {
                $table->dropColumn('email_verified_at');
            }
        });
    }
};
