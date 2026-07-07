<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devoluciones', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20)->unique();
            $table->enum('tipo_documento', ['remision', 'venta'])->comment('De qué tipo de documento se devuelve');
            $table->unsignedBigInteger('documento_id')->comment('remisiones.id o ventas.id');
            $table->foreignId('cliente_id')
                ->constrained('clientes')
                ->onDelete('restrict');
            $table->enum('estado', ['borrador', 'confirmada', 'anulada'])
                ->default('borrador');
            $table->timestamp('confirmada_en')->nullable();
            $table->enum('motivo', ['cambio', 'defecto', 'error_pedido', 'otro'])->nullable();
            $table->text('observaciones')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('descuento', 15, 2)->default(0);
            $table->decimal('impuestos', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->foreignId('usuario_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index('tipo_documento');
            $table->index('cliente_id');
            $table->index('estado');
            $table->index(['tipo_documento', 'documento_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devoluciones');
    }
};
