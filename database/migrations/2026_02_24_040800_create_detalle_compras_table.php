<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla: detalle_compras
 *
 * Correcciones vs original:
 *   - Nombre: detallesCompras → detalle_compras  (snake_case)
 *   - compraId     → compra_id   (snake_case)
 *   - inventarioId → producto_id (apunta a productos)
 *   - cantidad: integer → decimal(10,3)  — y se elimina el .change() incorrecto
 *     (.change() es para ALTER TABLE, no para CREATE TABLE)
 *   - precio_unitario: decimal(15,2)
 *   - Agregado: descuento_unitario, impuesto_id, subtotal
 *   - down() corregido al nombre real de la tabla
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_compras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compra_id')
                ->constrained('compras')
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
            $table->timestamps();

            $table->index('compra_id', 'det_compra_idx');
            $table->index('producto_id', 'det_compra_producto_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_compras');
    }
};
