<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla: compras
 *
 * Estados: borrador -> registrada -> pendiente -> pagada
 *                                            \-> anulada (terminal, con rollback)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compras', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20)->unique();
            $table->enum('estado', ['borrador', 'registrada', 'pendiente', 'pagada', 'anulada'])
                ->default('borrador')
                ->comment('Ciclo de vida del documento');
            $table->timestamp('confirmada_en')
                ->nullable()
                ->comment('Fecha en que se registró la compra');
            $table->foreignId('proveedor_id')
                ->constrained('proveedores')
                ->onDelete('restrict');
            $table->foreignId('bodega_id')
                ->constrained('bodegas')
                ->onDelete('restrict');
            $table->foreignId('usuario_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            $table->timestamp('fecha')->useCurrent();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('descuento', 15, 2)->default(0);
            $table->decimal('impuestos', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('total_confirmado', 15, 2)
                ->nullable()
                ->comment('Total capturado en el momento del registro');
            $table->decimal('impuestos_confirmados', 15, 2)
                ->nullable()
                ->comment('Impuestos capturados en el momento del registro');
            $table->json('snapshot_confirmacion')
                ->nullable()
                ->comment('Snapshot JSON de datos financieros al registrar');
            $table->decimal('saldo_pendiente', 15, 2)->default(0);
            $table->date('fecha_vencimiento')->nullable();
            $table->text('observaciones')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('fecha', 'compras_fecha_idx');
            $table->index('estado', 'compras_estado_idx');
            $table->index('usuario_id', 'compras_usuario_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compras');
    }
};
