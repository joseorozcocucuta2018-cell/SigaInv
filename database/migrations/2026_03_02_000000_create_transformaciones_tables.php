<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('transformaciones')) {
            Schema::create('transformaciones', function (Blueprint $t) {
                $t->id();
                $t->foreignId('bodega_id')->constrained('bodegas');

                // Tipos: combo, promo, reenvase, fabricacion
                $t->enum('tipo', ['combo', 'promo', 'reenvase', 'fabricacion'])
                    ->default('fabricacion');

                // Sub-tipo para promociones: descuento, cantidad, empaquetado
                $t->enum('tipo_promo', ['descuento', 'cantidad', 'empaquetado'])
                    ->nullable()
                    ->comment('Solo aplica si tipo = promo');

                // Fecha de vencimiento para promociones
                $t->dateTime('fecha_vencimiento')->nullable()
                    ->comment('Solo aplica si tipo = promo');

                $t->foreignId('producto_final_id')
                    ->nullable()
                    ->constrained('productos')
                    ->nullOnDelete()
                    ->comment('Producto que resulta de la transformación');
                $t->foreignId('formula_transformacion_id')
                    ->nullable()
                    ->constrained('formula_transformaciones')
                    ->nullOnDelete()
                    ->comment('Fórmula predefinida para esta transformación');
                $t->decimal('cantidad_a_producir', 10, 3)
                    ->default(1)
                    ->comment('Cuántos productos finales se desean crear con esta fórmula');

                // Pricing (propio de cada transformación — la fórmula es solo receta)
                $t->enum('tipo_calculo_precio', ['margen', 'manual'])->default('margen');
                $t->decimal('costo_total', 15, 2)->nullable();
                $t->decimal('precio_sugerido', 15, 2)->nullable();
                $t->decimal('margen_deseado', 5, 2)->default(30.00);

                // Estados: borrador, confirmada, revertida
                $t->enum('estado', ['borrador', 'confirmada', 'revertida'])
                    ->default('borrador');

                $t->dateTime('confirmada_en')->nullable();
                $t->dateTime('revertida_en')->nullable();
                $t->dateTime('fecha')->useCurrent();
                $t->text('observaciones')->nullable();
                $t->foreignId('usuario_id')->nullable()->constrained('users');
                $t->timestamps();
                $t->softDeletes();

                $t->index(['bodega_id', 'fecha'], 'transformaciones_bodega_fecha_idx');
                $t->index(['tipo', 'estado'], 'transformaciones_tipo_estado_idx');
                $t->index('fecha_vencimiento', 'transformaciones_fecha_vencimiento_idx');
            });
        }

        if (! Schema::hasTable('transformacion_detalles')) {
            Schema::create('transformacion_detalles', function (Blueprint $t) {
                $t->id();
                $t->foreignId('transformacion_id')
                    ->constrained('transformaciones')
                    ->onDelete('cascade');
                $t->enum('tipo_linea', ['insumo', 'producto'])->default('insumo');
                $t->foreignId('producto_id')->constrained('productos');
                $t->decimal('cantidad', 10, 3);
                $t->string('lote')->nullable();
                $t->string('serial')->nullable();
                $t->date('fecha_vencimiento')->nullable();
                $t->decimal('costo_unitario', 10, 2)->nullable();
                $t->timestamps();
                $t->softDeletes();

                $t->index(['transformacion_id', 'tipo_linea'], 'transformacion_detalles_tipo_idx');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('transformacion_detalles')) {
            Schema::dropIfExists('transformacion_detalles');
        }

        if (Schema::hasTable('transformaciones')) {
            Schema::dropIfExists('transformaciones');
        }
    }
};
