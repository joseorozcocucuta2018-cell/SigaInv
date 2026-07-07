<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla: ventas
 *
 * Correcciones vs original:
 *   - clienteId → cliente_id  (snake_case)
 *   - Agregado: numero         (consecutivo de factura)
 *   - Agregado: usuario_id     (vendedor — indispensable para comisiones y trazabilidad)
 *   - Agregado: bodega_id      (desde qué bodega se despacha)
 *   - Agregado: cotizacion_id  (trazabilidad del flujo comercial)
 *   - Agregado: remision_id    (trazabilidad del flujo comercial)
 *   - total: desglosado en subtotal + descuento + impuestos + total
 *   - decimal(15,2) en lugar de (10,2)
 *   - estado_pago ampliado: agrega 'parcial' y 'anulada'
 *   - Índice corregido: estado_Pago → estado_pago
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20)->unique();
            $table->enum('estado', ['borrador', 'confirmada', 'pagada', 'anulada'])
                ->default('borrador')
                ->comment('Ciclo de vida del documento');
            $table->timestamp('confirmada_en')
                ->nullable()
                ->comment('Fecha en que se confirmó la venta');
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
            $table->foreignId('cotizacion_id')
                ->nullable()
                ->constrained('cotizaciones')
                ->onDelete('set null');
            $table->foreignId('remision_id')
                ->nullable()
                ->constrained('remisiones')
                ->onDelete('set null');
            $table->timestamp('fecha')->useCurrent();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('descuento', 15, 2)->default(0);
            $table->decimal('impuestos', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('total_confirmado', 15, 2)
                ->nullable()
                ->comment('Total capturado en el momento de la confirmación');
            $table->decimal('impuestos_confirmados', 15, 2)
                ->nullable()
                ->comment('Impuestos capturados en el momento de la confirmación');
            $table->json('snapshot_confirmacion')
                ->nullable()
                ->comment('Snapshot JSON de datos financieros al confirmar');
            $table->decimal('saldo_pendiente', 15, 2)->default(0);
            $table->enum('estado_pago', ['pagado', 'pendiente', 'parcial', 'anulada'])
                ->default('pendiente');
            $table->date('fecha_vencimiento')->nullable();
            $table->text('observaciones')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('fecha', 'ventas_fecha_idx');
            $table->index('estado', 'ventas_estado_idx');
            $table->index(['cliente_id', 'estado_pago'], 'ventas_cliente_estado_idx');
            $table->index('usuario_id', 'ventas_usuario_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
