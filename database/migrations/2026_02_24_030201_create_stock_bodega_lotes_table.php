<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla: stock_bodega_lotes
 *
 * Desglose de stock por lote y fecha de vencimiento por cada registro de stock_bodegas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_bodega_lotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_bodega_id')
                ->constrained('stock_bodegas')
                ->onDelete('cascade');
            $table->string('lote')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->decimal('cantidad', 10, 3)->default(0);
            $table->timestamps();

            $table->index(['stock_bodega_id', 'lote'], 'sbl_lote_idx');
            $table->index('fecha_vencimiento', 'sbl_vence_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_bodega_lotes');
    }
};
