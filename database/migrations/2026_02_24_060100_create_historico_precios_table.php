<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla: historico_precios
 *
 * Objetivo: registro único por combinación producto+proveedor.
 * Muestra el último precio de compra de cada proveedor para un producto.
 * El Observer de compras hace upsert: si existe → actualiza precio_compra,
 * si no existe → inserta.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historico_precios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')
                ->constrained('productos')
                ->onDelete('cascade');
            $table->foreignId('proveedor_id')
                ->nullable()
                ->constrained('proveedores')
                ->onDelete('cascade')
                ->comment('Proveedor que suministra este producto');
            $table->decimal('precio_compra', 15, 2);
            $table->foreignId('usuario_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            $table->timestamp('fecha_cambio')->useCurrent();
            $table->timestamps();

            $table->unique(['producto_id', 'proveedor_id'], 'histprecio_producto_proveedor_unique');
            $table->index('fecha_cambio', 'histprecio_fecha_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historico_precios');
    }
};
