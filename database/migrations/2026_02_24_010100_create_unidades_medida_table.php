<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla: unidades_medida
 *
 * Correcciones vs original:
 *   - Nombre tabla: unidadesMedidas → unidades_medida  (snake_case singular, convención Laravel)
 *   - nombre: varchar(255) → varchar(60)  (nombre de unidad no necesita 255 chars)
 *   - down(): dropIfExists tenía nombre incorrecto 'unidades_medidas' (plural con s extra)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unidades_medida', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 60);
            $table->string('simbolo', 20);
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unidades_medida');
    }
};
