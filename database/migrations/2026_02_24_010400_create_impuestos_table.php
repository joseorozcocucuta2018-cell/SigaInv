<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla: impuestos
 *
 * Correcciones y mejoras vs original:
 *   - nombre: varchar(255) → varchar(100)  (no necesita 255)
 *   - Agregado: activo
 *   - Agregado: tipo ENUM — distingue IVA de retención de ICO, etc.
 *     Contexto Colombia: IVA (0%, 5%, 19%), INC, ICO, ReteIVA, ReteICA, ReteRenta
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('impuestos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->enum('tipo', ['IVA', 'INC', 'ICO', 'ReteIVA', 'ReteICA', 'ReteRenta', 'Otro'])
                ->default('IVA');
            $table->decimal('porcentaje', 5, 2);
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('impuestos');
    }
};
