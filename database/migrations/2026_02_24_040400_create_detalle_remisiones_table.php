<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla: detalle_remisiones
 *
 * Correcciones vs original:
 *   - Nombre: detallesRemisiones → detalle_remisiones  (snake_case)
 *   - remisionId   → remision_id  (snake_case)
 *   - inventarioId → producto_id  (apunta a productos)
 *   - cantidad: integer → decimal(10,3)
 *   - precio_unitario: decimal(15,2)
 *   - Agregado: descuento_unitario, impuesto_id, subtotal
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_remisiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('remision_id')
                ->constrained('remisiones')
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
            $table->decimal('costo_unitario', 15, 4)->nullable();
            $table->foreignId('impuesto_id')
                ->nullable()
                ->constrained('impuestos')
                ->onDelete('set null');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->timestamps();

            $table->index('remision_id', 'det_remision_idx');
            $table->index('producto_id', 'det_remision_producto_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_remisiones');
    }
};
