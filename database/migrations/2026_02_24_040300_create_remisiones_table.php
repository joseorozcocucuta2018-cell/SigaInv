<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla: remisiones
 *
 * Correcciones vs original:
 *   - clienteId → cliente_id  (snake_case)
 *   - Agregado: numero
 *   - Agregado: usuario_id
 *   - Agregado: bodega_id     (desde qué bodega sale la mercancía)
 *   - Agregado: cotizacion_id (trazabilidad: de qué cotización viene esta remisión)
 *   - Agregado: observaciones
 *   - totales: decimal(15,2)
 *   - estado_pago ampliado: agrega 'anulada'
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('remisiones', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20)->unique();
            $table->enum('estado', ['borrador', 'confirmada', 'facturada', 'anulada'])
                ->default('borrador')
                ->comment('Ciclo de vida del documento');
            $table->timestamp('confirmada_en')
                ->nullable()
                ->comment('Fecha en que se confirmó la remisión');
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

            $table->index('cliente_id', 'remisiones_cliente_idx');
            $table->index('estado', 'remisiones_estado_idx');
            $table->index('estado_pago', 'remisiones_estado_pago_idx');
            $table->index('fecha', 'remisiones_fecha_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('remisiones');
    }
};
