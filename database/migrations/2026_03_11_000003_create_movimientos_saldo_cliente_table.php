<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos_saldo_cliente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')
                ->constrained('clientes')
                ->onDelete('restrict');
            $table->enum('tipo', ['compra', 'venta', 'devolucion', 'pago', 'ajuste']);
            $table->string('referencia', 50)->comment('ej: venta_id, remision_id, devolucion_id');
            $table->decimal('monto', 15, 2)->comment('Positivo: aumenta deuda, Negativo: disminuye/crea crédito');
            $table->decimal('saldo_anterior', 15, 2);
            $table->decimal('saldo_nuevo', 15, 2);
            $table->text('descripcion')->nullable();
            $table->foreignId('usuario_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            $table->timestamps();

            $table->index('cliente_id');
            $table->index('tipo');
            $table->index('referencia');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_saldo_cliente');
    }
};
