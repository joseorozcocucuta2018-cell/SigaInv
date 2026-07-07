<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla: ciudades
 *
 * Corrección aplicada vs coredata original:
 *   - departamentosId → departamento_id  (snake_case, convención Laravel/Eloquent)
 *   - down() implementado correctamente (original estaba vacío)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ciudades', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->foreignId('departamento_id')
                ->constrained('departamentos')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ciudades');
    }
};
