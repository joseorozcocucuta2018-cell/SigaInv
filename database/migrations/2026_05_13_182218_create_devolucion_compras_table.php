<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devoluciones_compras', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 30)->unique();
            $table->foreignId('compra_id')->constrained('compras');
            $table->foreignId('proveedor_id')->constrained('proveedores');
            $table->foreignId('bodega_id')->constrained('bodegas');
            $table->enum('estado', ['borrador', 'confirmada', 'anulada'])->default('borrador');
            $table->string('motivo', 50)->nullable();
            $table->date('fecha');
            $table->text('observaciones')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('descuento', 15, 2)->default(0);
            $table->decimal('impuestos', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->dateTime('confirmada_en')->nullable();
            $table->dateTime('anulada_en')->nullable();
            $table->foreignId('usuario_id')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('detalles_devoluciones_compras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('devolucion_compra_id')->constrained('devoluciones_compras');
            $table->foreignId('producto_id')->constrained('productos');
            $table->decimal('cantidad', 15, 3)->default(1);
            $table->decimal('precio_unitario', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('costo_unitario', 15, 4)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalles_devoluciones_compras');
        Schema::dropIfExists('devoluciones_compras');
    }
};
