<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_ajustes_inventario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ajuste_inventario_id')
                ->constrained('ajustes_inventario')
                ->onDelete('cascade');
            $table->foreignId('producto_id')
                ->constrained('productos')
                ->onDelete('restrict');
            $table->decimal('stock_sistema', 15, 3)->default(0);
            $table->decimal('stock_fisico', 15, 3)->default(0);
            $table->decimal('diferencia', 15, 3)->default(0);
            $table->decimal('costo_unitario', 15, 4)->default(0);
            $table->timestamps();

            $table->index('ajuste_inventario_id');
            $table->index('producto_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_ajustes_inventario');
    }
};
