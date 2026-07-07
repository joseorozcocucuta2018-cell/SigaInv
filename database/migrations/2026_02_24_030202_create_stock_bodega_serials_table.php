<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla: stock_bodega_serials
 *
 * Seriales por registro de stock_bodegas (productos con control por serial).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_bodega_serials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_bodega_id')
                ->constrained('stock_bodegas')
                ->onDelete('cascade');
            $table->string('serial')->unique();
            $table->string('status')->default('available');
            $table->string('lote')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->timestamps();

            $table->index(['stock_bodega_id', 'status'], 'sbs_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_bodega_serials');
    }
};
