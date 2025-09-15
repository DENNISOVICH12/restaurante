<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('menu_items')) {
            return;
        }

        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->text('nombre');
            $table->text('descripcion')->nullable();
            $table->decimal('precio', 10, 2);
            $table->text('imagen')->nullable();
            $table->string('categoria');
            $table->boolean('disponible')->default(true);
            $table->timestampTz('fecha_creacion')->useCurrent();
            $table->timestampTz('fecha_actualizacion')->useCurrent()->useCurrentOnUpdate();

            $table->index('categoria');
            $table->index('disponible');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
