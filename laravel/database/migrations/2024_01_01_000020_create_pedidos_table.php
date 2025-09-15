<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pedidos')) {
            return;
        }

        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_cliente')->nullable()->constrained('clientes')->nullOnDelete();
            $table->timestampTz('fecha')->useCurrent();
            $table->string('estado')->default('pendiente');
            $table->string('mesa')->nullable();

            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
