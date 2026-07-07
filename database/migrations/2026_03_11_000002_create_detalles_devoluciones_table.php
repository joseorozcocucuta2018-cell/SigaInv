<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalles_devoluciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('devolucion_id')
                ->constrained('devoluciones')
                ->onDelete('cascade');
            $table->foreignId('producto_id')
                ->constrained('productos')
                ->onDelete('restrict');
            $table->unsignedInteger('cantidad')->comment('Cantidad devuelta');
            $table->decimal('precio_unitario', 15, 2)->comment('Precio unitario en el documento original');
            $table->decimal('subtotal', 15, 2);
            $table->boolean('defectuoso')->default(false)->comment('¿Producto defectuoso? (para garantía)');
            $table->timestamps();

            $table->index('devolucion_id');
            $table->index('producto_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalles_devoluciones');
    }
};
