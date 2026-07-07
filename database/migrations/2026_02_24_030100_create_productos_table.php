<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla: productos  (NUEVA — reemplaza la lógica de catálogo de 'inventarios')
 *
 * Separación de responsabilidades:
 *   - productos     → QUÉ existe (catálogo, atributos, precios de referencia)
 *   - stock_bodegas → CUÁNTO hay y DÓNDE (cantidad por bodega)
 *
 * Correcciones vs 'inventarios' original:
 *   - Eliminados: cantidad, ubicacion, bodega_id  (van en stock_bodegas)
 *   - impuesto_id, categoria_id, marca_id, unidad_medida_id → snake_case
 *   - precio_compra / precio_venta: decimal(15,2) en lugar de (10,2)
 *   - cantidad en inventarios era integer — productos no tiene cantidad aquí
 *   - Agregado: codigo (SKU/código de barras — búsqueda rápida)
 *   - Agregado: codigo_barras
 *   - Agregado: stock_minimo / stock_maximo (umbrales de alerta)
 *   - Agregado: activo
 *   - Agregado: imagen
 *   - Agregado: usuario_id
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique();
            $table->string('codigo_barras', 50)->nullable()->unique();
            $table->string('nombre', 150);
            $table->string('nombre_comun', 150)->nullable();
            $table->enum('tipo_producto', ['comprado', 'manufacturado', 'materia_prima', 'servicio'])
                ->default('comprado')
                ->comment('Tipo de producto: comprado, manufacturado, materia_prima, servicio');
            $table->text('descripcion')->nullable();
            $table->decimal('precio_compra', 15, 2)->default(0);
            $table->decimal('costo_promedio', 15, 4)->default(0)->comment('Costo promedio ponderado - se calcula al confirmar compras');
            $table->decimal('precio_venta', 15, 2)->default(0);
            $table->decimal('stock_minimo', 10, 3)->default(0);
            $table->decimal('stock_maximo', 10, 3)->default(0);
            $table->string('imagen', 255)->nullable();
            $table->boolean('activo')->default(true);
            $table->boolean('con_formula')->default(false);
            $table->boolean('tiene_movimientos')->default(false)->comment('Indica si el producto ya tiene transacciones (lo hace inmutable)');
            $table->boolean('exige_lote')->default(false);
            $table->boolean('exige_serial')->default(false);
            $table->foreignId('categoria_id')
                ->default(1)
                ->constrained('categorias')
                ->onDelete('restrict');
            $table->foreignId('marca_id')
                ->default(1)
                ->constrained('marcas')
                ->onDelete('restrict');
            $table->foreignId('unidad_medida_id')
                ->default(1)
                ->constrained('unidades_medida')
                ->onDelete('restrict');
            $table->foreignId('impuesto_id')
                ->default(1)
                ->constrained('impuestos')
                ->onDelete('restrict');
            $table->foreignId('usuario_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            $table->timestamps();

            $table->index('codigo', 'productos_codigo_idx');
            $table->index('nombre', 'productos_nombre_idx');
            $table->index('categoria_id', 'productos_categoria_idx');
            $table->index('activo', 'productos_activo_idx');
            $table->index('tipo_producto', 'productos_tipo_producto_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
