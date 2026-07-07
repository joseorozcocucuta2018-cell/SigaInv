<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ajustes_inventario', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20)->unique();
            $table->foreignId('bodega_id')->constrained('bodegas')->onDelete('restrict');
            $table->foreignId('usuario_id')->nullable()->constrained('users')->onDelete('set null');
            $table->date('fecha');
            $table->string('motivo', 30);
            $table->enum('estado', ['borrador', 'confirmado', 'anulado'])->default('borrador');
            $table->boolean('es_saldo_inicial')->default(false);
            $table->text('observacion')->nullable();
            $table->timestamp('confirmado_en')->nullable();
            $table->timestamps();
            $table->index('bodega_id');
            $table->index('fecha');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ajustes_inventario');
    }
};
