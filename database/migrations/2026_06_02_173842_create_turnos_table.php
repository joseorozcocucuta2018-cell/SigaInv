<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('turnos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caja_id')->constrained('cajas');
            $table->foreignId('bodega_id')
                ->nullable()
                ->default(1)
                ->constrained('bodegas')
                ->onDelete('set null');
            $table->foreignId('usuario_id')->constrained('users');
            $table->dateTime('fecha_apertura');
            $table->dateTime('fecha_cierre')->nullable();
            $table->decimal('saldo_inicial', 15, 2)->default(0);
            $table->decimal('saldo_final_esperado', 15, 2)->nullable();
            $table->decimal('saldo_final_real', 15, 2)->nullable();
            $table->decimal('diferencia', 15, 2)->nullable();
            $table->enum('estado', ['abierto', 'cerrado'])->default('abierto');
            $table->timestamps();

            $table->index(['caja_id', 'estado'], 'turnos_caja_estado_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('turnos');
    }
};
