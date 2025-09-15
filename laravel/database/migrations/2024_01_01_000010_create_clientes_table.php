<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('clientes')) {
            return;
        }

        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->text('nombre');
            $table->text('telefono')->nullable();
            $table->text('direccion')->nullable();
            $table->timestampTz('fecha_registro')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
