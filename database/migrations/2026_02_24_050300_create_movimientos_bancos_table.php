<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla: movimientos_bancos
 *
 * Correcciones vs original ('detallesBancos'):
 *   - Nombre: detallesBancos → movimientos_bancos  (snake_case + nombre descriptivo)
 *   - bancoId        → banco_id         (snake_case)
 *   - usuarioId      → usuario_id       (snake_case)
 *   - fechaMovimiento → fecha_movimiento (snake_case)
 *   - saldoActual    → saldo_actual      (snake_case)
 *   - monto: eliminado ->unsigned() en decimal (no existe en Laravel para decimales)
 *   - onDelete usuario: cascade → set null  (no tiene sentido bloquear movimientos
 *                                            históricos si se elimina un usuario)
 *   - Agregado: forma_pago_id           (qué método generó el movimiento)
 *   - Agregado: documento_tipo + documento_id (referencia al origen: pago_cliente, etc.)
 *   - Agregado: concepto               (descripción libre del movimiento)
 *   - down(): estaba completamente vacío — ahora tiene dropIfExists
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos_bancos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('banco_id')
                ->constrained('bancos')
                ->onDelete('restrict');
            $table->foreignId('usuario_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            $table->foreignId('forma_pago_id')
                ->nullable()
                ->constrained('formas_pago')
                ->onDelete('set null');
            $table->dateTime('fecha_movimiento');
            $table->enum('tipo', ['deposito', 'retiro', 'transferencia']);
            $table->decimal('monto', 15, 2);
            $table->decimal('saldo_actual', 15, 2)->comment('Saldo después del movimiento');
            $table->string('referencia', 100)->nullable();
            $table->string('concepto', 255)->nullable();
            $table->string('traslado_destino_tipo', 10)->nullable();
            $table->unsignedBigInteger('traslado_destino_id')->nullable();
            $table->string('documento_tipo', 30)->nullable();
            $table->unsignedBigInteger('documento_id')->nullable();
            $table->timestamps();

            $table->index(['banco_id', 'fecha_movimiento'], 'movbanco_banco_fecha_idx');
            $table->index('tipo', 'movbanco_tipo_idx');
            $table->index(['documento_tipo', 'documento_id'], 'movbanco_documento_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_bancos');
    }
};
