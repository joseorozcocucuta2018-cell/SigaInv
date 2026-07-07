<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conteos_fisicos', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20)->unique();
            $table->foreignId('bodega_id')->constrained('bodegas')->onDelete('restrict');
            $table->foreignId('usuario_id')->nullable()->constrained('users')->onDelete('set null');
            $table->date('fecha_inicio');
            $table->date('fecha_cierre')->nullable();
            $table->enum('estado', ['abierto', 'cerrado', 'ajustado'])->default('abierto');
            $table->boolean('es_saldo_inicial')->default(false);
            $table->text('observacion')->nullable();
            $table->timestamps();
            $table->index('bodega_id');
            $table->index('fecha_inicio');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conteos_fisicos');
    }
};
