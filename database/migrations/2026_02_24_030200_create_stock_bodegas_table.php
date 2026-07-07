<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla: stock_bodegas  (NUEVA)
 *
 * Pivote productos × bodegas — registra el stock actual de cada
 * producto en cada bodega de forma independiente.
 *
 * Por qué es necesaria:
 *   El original mezclaba producto + stock en una sola tabla 'inventarios'.
 *   Si el mismo producto existe en 3 bodegas, el original creaba 3 registros
 *   con IDs diferentes — rompiendo la integridad al registrar ventas/compras.
 *
 * cantidad: decimal(10,3) — soporta productos fraccionados (metros, kilos, litros)
 * ubicacion: posición física dentro de la bodega (estante, pasillo, etc.)
 *
 * La combinación (producto_id, bodega_id) es única:
 *   un producto solo tiene UN registro de stock por bodega.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_bodegas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')
                ->constrained('productos')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table->foreignId('bodega_id')
                ->constrained('bodegas')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table->decimal('cantidad', 10, 3)->default(0);
            $table->string('ubicacion', 50)->nullable();
            $table->timestamps();

            // Un producto tiene un único registro de stock por bodega
            $table->unique(['producto_id', 'bodega_id'], 'stock_producto_bodega_unique');
            $table->index(['bodega_id', 'producto_id'], 'stock_bodega_producto_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_bodegas');
    }
};
