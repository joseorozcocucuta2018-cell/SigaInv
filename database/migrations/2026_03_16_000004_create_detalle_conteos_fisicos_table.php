<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_conteos_fisicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conteo_fisico_id')
                ->constrained('conteos_fisicos')
                ->onDelete('cascade');
            $table->foreignId('producto_id')
                ->constrained('productos')
                ->onDelete('restrict');
            $table->decimal('stock_sistema', 15, 3)->default(0);
            $table->decimal('cantidad_contada', 15, 3)->nullable();
            $table->decimal('diferencia', 15, 3)->default(0);
            $table->foreignId('ajuste_inventario_id')
                ->nullable()
                ->constrained('ajustes_inventario')
                ->onDelete('set null');
            $table->timestamps();

            $table->index('conteo_fisico_id');
            $table->index('producto_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_conteos_fisicos');
    }
};
