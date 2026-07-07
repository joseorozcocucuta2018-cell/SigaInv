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
        Schema::create('notas', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['nota_credito', 'nota_debito']);
            $table->string('numero', 20)->unique();
            $table->foreignId('venta_id')->nullable()->constrained('ventas')->onDelete('cascade');
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->timestamp('fecha');
            $table->string('motivo', 255);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('impuestos', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->enum('estado', ['borrador', 'confirmada', 'anulada'])->default('borrador');
            $table->timestamp('confirmada_en')->nullable();
            $table->foreignId('usuario_id')->constrained('users');
            $table->string('xml_path', 255)->nullable();
            $table->string('pdf_path', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('detalle_notas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nota_id')->constrained('notas')->onDelete('cascade');
            $table->foreignId('producto_id')->constrained('productos');
            $table->decimal('cantidad', 10, 3);
            $table->decimal('precio_unitario', 15, 2);
            $table->decimal('impuestos', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_notas');
        Schema::dropIfExists('notas');
    }
};
