<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla: bodegas
 *
 * Correcciones vs original:
 *   - ciudades_Id     → ciudad_id      (snake_case uniforme, sin mayúscula en Id)
 *   - departamentos_Id → departamento_id (snake_case uniforme)
 *   - onDelete: cascade → restrict     (no tiene sentido borrar una bodega en cascada
 *                                       si se elimina un departamento o ciudad)
 *   - direccion2: ahora nullable()     (dirección secundaria es opcional)
 *   - Agregado: activo
 *   - Agregado: usuario_id             (quién registró la bodega)
 *   - Agregado: descripcion
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bodegas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();
            $table->text('descripcion')->nullable();
            $table->string('direccion1', 100);
            $table->string('direccion2', 100)->nullable();
            $table->foreignId('departamento_id')
                ->constrained('departamentos')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table->foreignId('ciudad_id')
                ->constrained('ciudades')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table->boolean('es_principal')->default(false);
            $table->tinyInteger('numero_cajas_pos')
                ->unsigned()
                ->default(0)
                ->comment('Cantidad de cajas POS asociadas a esta bodega/sucursal');
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->foreignId('usuario_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bodegas');
    }
};
