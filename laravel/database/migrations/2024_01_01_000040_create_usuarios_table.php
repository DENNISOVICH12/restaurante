<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('usuarios')) {
            return;
        }

        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('correo')->unique();
            $table->string('password');
            $table->string('rol')->default('mesero');
            $table->boolean('activo')->default(true);
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
