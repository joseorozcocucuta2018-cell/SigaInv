<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('traslados', function (Blueprint $t) {
            $t->id();
            $t->foreignId('bodega_origen_id')->constrained('bodegas');
            $t->foreignId('bodega_destino_id')->constrained('bodegas');

            // Estados: borrador, confirmada, revertida, anulada
            $t->enum('estado', ['borrador', 'confirmada', 'revertida', 'anulada'])
                ->default('borrador');

            $t->dateTime('confirmada_en')->nullable();
            $t->dateTime('revertida_en')->nullable();
            $t->dateTime('fecha')->useCurrent();
            $t->text('observaciones')->nullable();
            $t->foreignId('usuario_id')->nullable()->constrained('users');
            $t->timestamps();
            $t->softDeletes();

            $t->index(['bodega_origen_id', 'fecha'], 'traslados_origen_fecha_idx');
            $t->index(['bodega_destino_id', 'fecha'], 'traslados_destino_fecha_idx');
            $t->index('estado', 'traslados_estado_idx');
        });

        Schema::create('traslado_detalles', function (Blueprint $t) {
            $t->id();
            $t->foreignId('traslado_id')
                ->constrained('traslados')
                ->onDelete('cascade');
            $t->foreignId('producto_id')->constrained('productos');
            $t->decimal('cantidad', 10, 3);
            $t->string('lote')->nullable();
            $t->date('fecha_vencimiento')->nullable();
            $t->timestamps();
            $t->softDeletes();

            $t->index(['traslado_id', 'producto_id'], 'traslado_detalles_producto_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('traslado_detalles');
        Schema::dropIfExists('traslados');
    }
};
