<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla: detalle_cotizaciones
 *
 * Correcciones vs original:
 *   - Nombre: detallesCotizaciones → detalle_cotizaciones  (snake_case)
 *   - cotizacionId  → cotizacion_id  (snake_case)
 *   - inventarioId  → producto_id    (apunta a productos, no a inventarios)
 *   - cantidad: integer → decimal(10,3)  (soporta fraccionados)
 *   - precio_unitario: decimal(15,2)
 *   - Agregado: descuento_unitario   (descuento por línea)
 *   - Agregado: impuesto_id          (el impuesto puede variar por producto en la línea)
 *   - Agregado: subtotal             (cantidad × precio − descuento, calculado al guardar)
 *   - down() corregido al nombre real de la tabla
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_cotizaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cotizacion_id')
                ->constrained('cotizaciones')
                ->onDelete('cascade');
            $table->foreignId('producto_id')
                ->constrained('productos')
                ->onDelete('restrict');
            $table->string('lote')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->decimal('cantidad', 10, 3);
            $table->decimal('precio_unitario', 15, 2);
            $table->decimal('descuento_unitario', 15, 2)->default(0);
            $table->foreignId('impuesto_id')
                ->nullable()
                ->constrained('impuestos')
                ->onDelete('set null');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->timestamps();

            $table->index('cotizacion_id', 'det_cotizacion_idx');
            $table->index('producto_id', 'det_cotizacion_producto_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_cotizaciones');
    }
};
