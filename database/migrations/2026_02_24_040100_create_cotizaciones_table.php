<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla: cotizaciones
 *
 * Correcciones vs original:
 *   - clienteId → cliente_id  (snake_case)
 *   - Agregado: numero        (consecutivo — referencia visible para el cliente)
 *   - Agregado: usuario_id    (quién generó la cotización)
 *   - Agregado: bodega_id     (desde qué bodega se cotiza el stock)
 *   - Agregado: fecha_vigencia (las cotizaciones vencen)
 *   - Agregado: observaciones
 *   - total: decimal(15,2) en lugar de (10,2)
 *   - Índices para consultas frecuentes
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cotizaciones', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20)->unique();
            $table->foreignId('cliente_id')
                ->constrained('clientes')
                ->onDelete('restrict');
            $table->foreignId('bodega_id')
                ->constrained('bodegas')
                ->onDelete('restrict');
            $table->foreignId('usuario_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            $table->timestamp('fecha')->useCurrent();
            $table->date('fecha_vigencia')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('descuento', 15, 2)->default(0);
            $table->decimal('impuestos', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->enum('estado', ['pendiente', 'enviada', 'aceptada', 'rechazada', 'vencida'])
                ->default('pendiente');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index('cliente_id', 'cotizaciones_cliente_idx');
            $table->index('estado', 'cotizaciones_estado_idx');
            $table->index('fecha', 'cotizaciones_fecha_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cotizaciones');
    }
};
