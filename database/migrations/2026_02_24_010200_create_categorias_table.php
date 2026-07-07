<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla: categorias
 *
 * Correcciones y mejoras vs original:
 *   - Agregado: activo (gestión sin borrado físico)
 *   - Agregado: categoria_id (FK self-referencial — permite subcategorías)
 *     Ej: Electrónica > Computadores > Portátiles
 *     nullable() porque las categorías raíz no tienen padre
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_id')
                ->nullable()
                ->constrained('categorias')
                ->onDelete('restrict');
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categorias');
    }
};
