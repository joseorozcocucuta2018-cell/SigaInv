<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos_cajas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caja_id')
                ->constrained('cajas')
                ->onDelete('restrict');
            $table->foreignId('usuario_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            $table->foreignId('forma_pago_id')
                ->nullable()
                ->constrained('formas_pago')
                ->onDelete('set null');
            $table->dateTime('fecha_movimiento');
            $table->enum('tipo', ['ingreso', 'egreso', 'traslado']);
            $table->decimal('monto', 15, 2);
            $table->decimal('saldo_actual', 15, 2)->comment('Saldo después del movimiento');
            $table->string('categoria', 50)->nullable()->comment('gasto_operativo, ingreso_operativo, etc.');
            $table->string('referencia', 100)->nullable();
            $table->string('concepto', 255)->nullable();
            $table->string('traslado_destino_tipo', 10)->nullable();
            $table->unsignedBigInteger('traslado_destino_id')->nullable();
            $table->string('documento_tipo', 30)->nullable();
            $table->unsignedBigInteger('documento_id')->nullable();
            $table->timestamps();

            $table->index(['caja_id', 'fecha_movimiento'], 'movcaja_caja_fecha_idx');
            $table->index('tipo', 'movcaja_tipo_idx');
            $table->index(['documento_tipo', 'documento_id'], 'movcaja_documento_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_cajas');
    }
};
