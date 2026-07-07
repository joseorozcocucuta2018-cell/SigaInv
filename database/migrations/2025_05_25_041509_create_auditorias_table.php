<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla: auditorias
 *
 * Correcciones aplicadas vs coredata original:
 *   - registroId      → registro_id     (snake_case)
 *   - Agregado: usuario_id              (FK a users, nullable + set null)
 *     El original no tenía quién hizo el cambio — solo qué cambió.
 *     Sin usuario_id la auditoría es incompleta e inutilizable.
 *   - datos_anteriores y datos_nuevos: json (el original ya lo tenía correcto)
 *   - Índice de registroId corregido a nombre real de columna: registro_id
 *   - Agregado índice compuesto (tabla, registro_id) para búsquedas por entidad
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditorias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            $table->string('tabla', 50);
            $table->enum('operacion', ['INSERT', 'UPDATE', 'DELETE']);
            $table->unsignedBigInteger('registro_id');
            $table->json('datos_anteriores')->nullable();
            $table->json('datos_nuevos')->nullable();
            $table->timestamp('fecha_operacion')->useCurrent();
            $table->timestamps();

            // Índices para consultas frecuentes de auditoría
            $table->index('usuario_id');
            $table->index('tabla');
            $table->index('operacion');
            $table->index('fecha_operacion');
            $table->index(['tabla', 'registro_id'], 'auditorias_tabla_registro_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditorias');
    }
};
