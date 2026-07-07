<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cajas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->enum('tipo', ['caja_general', 'caja_menor', 'caja_sucursal', 'caja_pos'])->default('caja_general');
            $table->enum('estado', ['activa', 'inactiva'])->default('activa');
            $table->decimal('saldo_inicial', 15, 2)->default(0);
            $table->boolean('activo')->default(true);
            $table->foreignId('usuario_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['tipo', 'activo'], 'cajas_tipo_activo_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cajas');
    }
};
