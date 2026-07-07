<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla: detalle_ventas
 *
 * Correcciones vs original:
 *   - Nombre: detallesVentas → detalle_ventas  (snake_case)
 *   - ventaId      → venta_id    (snake_case)
 *   - inventarioId → producto_id (apunta a productos)
 *   - cantidad: integer → decimal(10,3)
 *   - precio_unitario: decimal(15,2)
 *   - Agregado: descuento_unitario, impuesto_id, subtotal, costo_unitario
 *   - down() corregido al nombre real de la tabla
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')
                ->constrained('ventas')
                ->onDelete('cascade');
            $table->foreignId('producto_id')
                ->constrained('productos')
                ->onDelete('restrict');
            $table->string('lote')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->string('serial')->nullable();
            $table->decimal('cantidad', 10, 3);
            $table->decimal('precio_unitario', 15, 2);
            $table->decimal('descuento_unitario', 15, 2)->default(0);
            $table->foreignId('impuesto_id')
                ->nullable()
                ->constrained('impuestos')
                ->onDelete('set null');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('costo_unitario', 15, 4)->nullable()
                ->comment('Costo promedio del producto al momento de la venta (para cálculo de utilidades)');
            $table->timestamps();

            $table->index('venta_id', 'det_venta_idx');
            $table->index('producto_id', 'det_venta_producto_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_ventas');
    }
};
