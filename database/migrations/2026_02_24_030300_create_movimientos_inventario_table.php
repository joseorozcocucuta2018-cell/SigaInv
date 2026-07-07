<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla: movimientos_inventario  (NUEVA)
 *
 * Diario contable del inventario — registra CADA entrada y salida.
 * Sin esta tabla no hay trazabilidad: no se puede saber por qué
 * el stock llegó a un número determinado.
 *
 * tipo_movimiento:
 *   - entrada_compra    : ingreso por orden de compra
 *   - salida_venta      : despacho por venta
 *   - entrada_devolucion: devolución de cliente
 *   - salida_devolucion : devolución a proveedor
 *   - traslado_entrada  : llegada desde otra bodega
 *   - traslado_salida   : salida hacia otra bodega
 *   - ajuste_positivo   : ajuste de inventario (conteo físico, más)
 *   - ajuste_negativo   : ajuste de inventario (conteo físico, menos)
 *
 * documento_tipo + documento_id: referencia polimórfica al origen del movimiento
 *   Ej: ('venta', 15) → ventas.id = 15
 *       ('compra', 3) → compras.id = 3
 *
 * cantidad: siempre positiva — el tipo_movimiento indica la dirección
 * stock_resultante: stock en esa bodega DESPUÉS del movimiento (snapshot)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')
                ->constrained('productos')
                ->onDelete('restrict');
            $table->foreignId('bodega_id')
                ->constrained('bodegas')
                ->onDelete('restrict');
            $table->enum('tipo_movimiento', [
                'saldo_inicial',
                'entrada_compra',
                'salida_venta',
                'salida_remision',
                'entrada_devolucion',
                'salida_devolucion',
                'traslado_entrada',
                'traslado_salida',
                'salida_traslado',
                'entrada_traslado',
                'reverso_traslado',
                'ajuste_positivo',
                'ajuste_negativo',
                'ajuste_costo_promedio',
                'ajuste_inicial',
                'ajuste_conteo',
                'reverso_anulacion',
                'facturacion_remision',
                'anulacion_venta_remision',
                'entrada_transformacion',
                'salida_transformacion',
                'reverso_transformacion',
            ]);
            $table->decimal('cantidad', 10, 3);
            $table->decimal('costo_unitario', 15, 2)->default(0);
            $table->string('lote')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->decimal('stock_resultante', 10, 3);
            $table->string('documento_tipo', 30)->nullable();   // 'venta', 'compra', etc.
            $table->unsignedBigInteger('documento_id')->nullable();
            $table->unsignedBigInteger('detalle_compra_id')->nullable();
            $table->unsignedBigInteger('detalle_venta_id')->nullable();
            $table->unsignedBigInteger('detalle_remision_id')->nullable();
            $table->text('observacion')->nullable();
            $table->foreignId('usuario_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            $table->timestamp('fecha_movimiento')->useCurrent();
            $table->timestamps();

            $table->index(['producto_id', 'bodega_id'], 'mov_producto_bodega_idx');
            $table->index(['documento_tipo', 'documento_id'], 'mov_documento_idx');
            $table->index('documento_id');
            $table->index('documento_tipo');
            $table->index('fecha_movimiento', 'mov_fecha_idx');
            $table->index('tipo_movimiento', 'mov_tipo_idx');
            $table->unique(
                ['documento_tipo', 'documento_id', 'detalle_compra_id', 'tipo_movimiento'],
                'movimientos_inventario_unique_compra_detalle'
            );
            $table->unique(
                ['documento_tipo', 'documento_id', 'detalle_venta_id', 'tipo_movimiento'],
                'movimientos_inventario_unique_venta_detalle'
            );
            $table->unique(
                ['documento_tipo', 'documento_id', 'detalle_remision_id', 'tipo_movimiento'],
                'movimientos_inventario_unique_remision_detalle'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_inventario');
    }
};
