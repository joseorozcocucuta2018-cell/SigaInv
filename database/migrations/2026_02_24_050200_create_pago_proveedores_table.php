<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla: pago_proveedores + detalle_pago_proveedores
 *
 * Sistema de pago en cascada (waterfall):
 *   - Un pago se registra contra un proveedor (no contra una compra individual)
 *   - El monto se distribuye automáticamente desde la compra más antigua
 *   - La tabla detalle_pago_proveedores registra cuánto se aplicó a cada compra
 *   - En proveedores es principalmente informativo
 *
 * numero: consecutivo del comprobante de egreso
 * monto: valor total pagado
 * banco_id: nullable — solo aplica si la forma_pago requiere banco
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pago_proveedores', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20)->unique();
            $table->foreignId('proveedor_id')
                ->constrained('proveedores')
                ->onDelete('restrict');
            $table->foreignId('forma_pago_id')
                ->constrained('formas_pago')
                ->onDelete('restrict');
            $table->foreignId('banco_id')
                ->nullable()
                ->constrained('bancos')
                ->onDelete('set null');
            $table->foreignId('caja_id')
                ->nullable()
                ->constrained('cajas')
                ->onDelete('set null');
            $table->foreignId('usuario_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            $table->timestamp('fecha')->useCurrent();
            $table->decimal('monto', 15, 2);
            $table->string('referencia', 100)->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index('proveedor_id', 'pagopro_proveedor_idx');
            $table->index('fecha', 'pagopro_fecha_idx');
        });

        Schema::create('detalle_pago_proveedores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pago_proveedor_id')
                ->constrained('pago_proveedores')
                ->onDelete('cascade');
            $table->foreignId('compra_id')
                ->constrained('compras')
                ->onDelete('restrict');
            $table->decimal('monto_aplicado', 15, 2);
            $table->timestamps();

            $table->index(['pago_proveedor_id'], 'detpagopro_pago_idx');
            $table->index(['compra_id'], 'detpagopro_compra_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_pago_proveedores');
        Schema::dropIfExists('pago_proveedores');
    }
};
