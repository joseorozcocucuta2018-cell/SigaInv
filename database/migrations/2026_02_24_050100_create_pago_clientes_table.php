<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla: pago_clientes + detalle_pago_clientes
 *
 * Sistema de pago en cascada (waterfall):
 *   - Un pago se registra contra un cliente (no contra un documento individual)
 *   - El monto se distribuye automáticamente desde la factura/remisión más antigua
 *   - La tabla detalle_pago_clientes registra cuánto se aplicó a cada documento
 *
 * numero: consecutivo del recibo de caja
 * monto: valor total recibido en este pago
 * banco_id: nullable — solo aplica si la forma_pago requiere banco
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pago_clientes', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20)->unique();
            $table->foreignId('cliente_id')
                ->constrained('clientes')
                ->onDelete('restrict');
            $table->foreignId('forma_pago_id')
                ->constrained('formas_pago')
                ->onDelete('restrict');
            $table->foreignId('banco_id')
                ->nullable()
                ->constrained('bancos')
                ->onDelete('set null');
            $table->foreignId('caja_id')
                ->nullable()
                ->constrained('cajas')
                ->onDelete('set null');
            $table->foreignId('usuario_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            $table->timestamp('fecha')->useCurrent();
            $table->decimal('monto', 15, 2);
            $table->string('referencia', 100)->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index('cliente_id', 'pagocli_cliente_idx');
            $table->index('fecha', 'pagocli_fecha_idx');
        });

        Schema::create('detalle_pago_clientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pago_cliente_id')
                ->constrained('pago_clientes')
                ->onDelete('cascade');
            $table->string('documento_tipo', 20)
                ->comment('venta o remision');
            $table->unsignedBigInteger('documento_id');
            $table->decimal('monto_aplicado', 15, 2);
            $table->timestamps();

            $table->index(['pago_cliente_id'], 'detpagocli_pago_idx');
            $table->index(['documento_tipo', 'documento_id'], 'detpagocli_doc_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_pago_clientes');
        Schema::dropIfExists('pago_clientes');
    }
};
