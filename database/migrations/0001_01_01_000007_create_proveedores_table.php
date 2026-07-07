<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla: proveedores
 *
 * Correcciones aplicadas vs coredata original:
 *   - ciudadesId      → ciudad_id       (snake_case)
 *   - departamentosId → departamento_id (snake_case)
 *   - usuarioId       → usuario_id      (snake_case)
 *   - usuario_id: onDelete cambiado de 'restrict' → 'set null' + nullable()
 *   - Orden de columnas corregido: FKs agrupadas lógicamente, usuario_id al final
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('documento', 20)->unique();
            $table->enum('tipo_documento', ['CC', 'NIT', 'CE', 'PP'])->default('NIT');
            $table->string('telefono', 30);
            $table->string('email', 100)->unique();
            $table->string('direccion1', 100);
            $table->string('direccion2', 100)->nullable();
            $table->foreignId('departamento_id')
                ->constrained('departamentos')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table->foreignId('ciudad_id')
                ->constrained('ciudades')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table->decimal('saldo', 15, 2)->default(0.00);           // Solo lectura — calculado por movimientos
            $table->string('pais', 100)->default('Colombia');
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->decimal('limite_credito', 15, 2)->default(0.00);
            $table->integer('dias_credito')->default(0);
            $table->integer('dias_pago')->default(0);
            $table->string('contacto_principal', 100)->nullable();
            $table->string('sitio_web', 255)->nullable();
            $table->foreignId('usuario_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proveedores');
    }
};
