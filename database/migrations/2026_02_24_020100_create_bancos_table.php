<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla: bancos
 *
 * Correcciones vs original:
 *   - nombreBanco    → nombre_banco   (snake_case)
 *   - numeroCuenta   → numero_cuenta  (snake_case)
 *   - tipoCuenta     → tipo_cuenta    (snake_case)
 *   - saldoInicial   → saldo_inicial  (snake_case)
 *   - codigoSwift    → codigo_swift   (snake_case)
 *   - Índices: nombres de columna actualizados a snake_case
 *   - Agregado: usuario_id — quién registró el banco (trazabilidad)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bancos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_banco', 100);
            $table->string('numero_cuenta', 50)->unique();
            $table->enum('tipo_cuenta', ['ahorros', 'corriente']);
            $table->decimal('saldo_inicial', 15, 2)->default(0);
            $table->string('moneda', 10)->default('COP');
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->string('codigo_swift', 20)->nullable()->comment('Para transacciones internacionales');
            $table->foreignId('usuario_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            $table->timestamps();

            $table->index(['nombre_banco', 'tipo_cuenta'], 'bancos_nombre_tipo_idx');
            $table->index(['nombre_banco', 'estado'], 'bancos_nombre_estado_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bancos');
    }
};
