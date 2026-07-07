<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('formula_transformaciones')) {
            Schema::create('formula_transformaciones', function (Blueprint $table) {
                $table->id();
                $table->text('descripcion')->nullable();
                $table->enum('tipo', ['combo', 'promo', 'reenvase', 'fabricacion'])->default('fabricacion');
                $table->string('producto_final_nombre', 255)->unique();
                $table->foreignId('producto_final_id')
                    ->nullable()
                    ->constrained('productos')
                    ->nullOnDelete();
                $table->decimal('cantidad_producto_final', 10, 3)->default(1);
                $table->boolean('activo')->default(true);
                $table->foreignId('usuario_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();
                $table->boolean('bloqueada')->default(false);
                $table->boolean('tiene_transformaciones')->default(false);
                $table->timestamps();

                $table->index('activo', 'formula_transformaciones_activo_idx');
                $table->index('tipo', 'formula_transformaciones_tipo_idx');
            });
        }

        if (! Schema::hasTable('formula_transformacion_detalles')) {
            Schema::create('formula_transformacion_detalles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('formula_transformacion_id')
                    ->constrained('formula_transformaciones')
                    ->onDelete('cascade')
                    ->index('ftdetalles_formula_fk');
                $table->enum('tipo_linea', ['insumo', 'producto'])->default('insumo');
                $table->foreignId('producto_id')
                    ->constrained('productos')
                    ->index('ftdetalles_producto_fk');
                $table->decimal('cantidad', 10, 3);
                $table->string('lote')->nullable();
                $table->date('fecha_vencimiento')->nullable();
                $table->decimal('costo_unitario', 15, 4)->nullable();
                $table->timestamps();

                $table->index(['formula_transformacion_id', 'tipo_linea'], 'ftdetalles_tipo_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('formula_transformacion_detalles');
        Schema::dropIfExists('formula_transformaciones');
    }
};
