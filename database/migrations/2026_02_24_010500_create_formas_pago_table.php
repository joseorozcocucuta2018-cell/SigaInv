<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla: formas_pago  (NUEVA — no existía en el original)
 *
 * Catálogo de métodos de pago reutilizable en:
 *   - pago_clientes  (cobros de ventas)
 *   - pago_proveedores (pagos de compras)
 *
 * Ejemplos: Efectivo, Transferencia, Cheque, Tarjeta débito,
 *           Tarjeta crédito, Nequi, Daviplata, Crédito directo
 *
 * requiere_banco: indica si el método exige seleccionar una cuenta bancaria
 *   Efectivo → false  |  Transferencia → true  |  Cheque → true
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('formas_pago', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 60)->unique();
            $table->boolean('requiere_banco')->default(false);
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formas_pago');
    }
};
