<?php

use App\Enums\DevolucionEstado;
use App\Enums\VentaEstado;
use App\Models\Bodega;
use App\Models\Ciudad;
use App\Models\Cliente;
use App\Models\Departamento;
use App\Models\DetalleDevolucion;
use App\Models\Devolucion;
use App\Models\MovimientoInventario;
use App\Models\MovimientoSaldoCliente;
use App\Models\Numeracion;
use App\Models\Producto;
use App\Models\StockBodega;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| DevolucionTest
|
| Verifica el comportamiento del módulo de devoluciones:
| - Modelo Devolucion: creación, numeración automática, estados
| - Relaciones: cliente, detalles
| - Confirmar devolución: restaura stock, actualiza saldo cliente
| - Confirmar registra MovimientoInventario con campos correctos
| - Confirmar registra MovimientoSaldoCliente
| - SoftDeletes funciona correctamente
| - DetalleDevolucion: relaciones y casts
| - MovimientoSaldoCliente: creación directa
|
| DOS CONTEXTOS DE VENTA:
|   A) Sin Numeracion activa → Venta::withoutEvents() + forceCreate()
|      Aísla el test de Devolucion de la dependencia de Numeracion.
|      Usado en la mayoría de tests para mantener foco en Devolucion.
|
|   B) Con Numeracion activa → crearNumeracionVenta() + Venta::create()
|      Valida el flujo real de extremo a extremo:
|      Numeracion → Venta (número generado) → Devolucion → stock/saldo.
|      Garantiza que el módulo funciona con el ciclo completo de negocio.
|--------------------------------------------------------------------------
*/

// ── Helpers ───────────────────────────────────────────────────────────────────

function setupDevolucion(): array
{
    $depto = Departamento::create(['nombre' => 'Depto Test DVL']);
    $ciudad = Ciudad::create(['nombre' => 'Ciudad Test DVL', 'departamento_id' => $depto->id]);

    $bodega = Bodega::create([
        'nombre' => 'Bodega DVL',
        'direccion1' => 'Calle 1',
        'departamento_id' => $depto->id,
        'ciudad_id' => $ciudad->id,
        'activo' => true,
    ]);

    $cliente = Cliente::create([
        'nombre' => 'Cliente DVL '.uniqid(),
        'documento' => 'CLI-'.uniqid(),
        'tipo_documento' => 'CC',
        'telefono' => '123',
        'email' => 'cli-'.uniqid().'@test.com',
        'direccion1' => 'Calle 1',
        'departamento_id' => $depto->id,
        'ciudad_id' => $ciudad->id,
        'saldo' => 500.00,
        'activo' => true,
        'portal_acceso' => 'sin_acceso',
    ]);

    $producto = Producto::create([
        'codigo' => 'DVL-'.uniqid(),
        'nombre' => 'Producto DVL',
        'precio_compra' => 100,
        'precio_venta' => 150,
        'costo_promedio' => 100,
        'activo' => true,
    ]);

    return compact('bodega', 'cliente', 'producto');
}

/**
 * Crea una Devolucion en estado borrador.
 * documento_id=0 es suficiente para tests que no ejerciten getBodyegaDeDocumento().
 */
function crearDevolucion(int $clienteId, array $extra = []): Devolucion
{
    return Devolucion::create(array_merge([
        'tipo_documento' => 'venta',
        'documento_id' => 0,
        'cliente_id' => $clienteId,
        'estado' => DevolucionEstado::BORRADOR,
        'motivo' => 'defecto',
        'subtotal' => 0,
        'descuento' => 0,
        'impuestos' => 0,
        'total' => 0,
    ], $extra));
}

/**
 * Crea una Numeracion activa para tipo 'venta'.
 * Permite que Venta::create() funcione normalmente en tests (contexto B).
 * La constraint única es (tipo_documento, anno), por lo que solo
 * puede existir una por año — usar una vez por test.
 */
function crearNumeracionVenta(): Numeracion
{
    return Numeracion::create([
        'tipo_documento' => 'venta',
        'resolucion_numero' => 'TEST-00001',
        'prefijo' => 'VEN',
        'consecutivo_desde' => 1,
        'consecutivo_hasta' => 9999,
        'consecutivo_actual' => 0,
        'anno' => now()->year,
    ]);
}

/**
 * Crea una Venta en estado CONFIRMADA sin pasar por el evento creating()
 * que requiere una Numeracion activa en BD.
 * withoutEvents() + forceCreate() es el patron correcto para esto.
 * Contexto A: tests aislados de Devolucion sin dependencia de Numeracion.
 */
function crearVentaConfirmada(int $clienteId, int $bodegaId, array $extra = []): Venta
{
    return Venta::withoutEvents(function () use ($clienteId, $bodegaId, $extra) {
        return Venta::forceCreate(array_merge([
            'numero' => 'VTA-TEST-'.uniqid(),
            'estado' => VentaEstado::CONFIRMADA,
            'cliente_id' => $clienteId,
            'bodega_id' => $bodegaId,
            'subtotal' => 0,
            'descuento' => 0,
            'impuestos' => 0,
            'total' => 0,
            'saldo_pendiente' => 0,
            'estado_pago' => 'pendiente',
            'fecha' => now(),
        ], $extra));
    });
}

// ── Modelo — numeración automática ────────────────────────────────────────────

it('Devolucion → se crea con número automático DVL-XXXXX', function () {
    ['cliente' => $cliente] = setupDevolucion();

    $devolucion = crearDevolucion($cliente->id);

    expect($devolucion->numero)->toMatch('/^DVL-\d{5}$/');
});

it('Devolucion → números consecutivos incrementan correctamente', function () {
    ['cliente' => $cliente] = setupDevolucion();

    $d1 = crearDevolucion($cliente->id);
    $d2 = crearDevolucion($cliente->id);

    $n1 = (int) explode('-', $d1->numero)[1];
    $n2 = (int) explode('-', $d2->numero)[1];

    expect($n2)->toBe($n1 + 1);
});

it('Devolucion → estado inicial es borrador', function () {
    ['cliente' => $cliente] = setupDevolucion();

    $devolucion = crearDevolucion($cliente->id);

    expect($devolucion->estado)->toBe(DevolucionEstado::BORRADOR);
});

// ── Modelo — relaciones ───────────────────────────────────────────────────────

it('Devolucion → relación cliente devuelve el cliente correcto', function () {
    ['cliente' => $cliente] = setupDevolucion();

    $devolucion = crearDevolucion($cliente->id);

    expect($devolucion->cliente->id)->toBe($cliente->id);
    expect($devolucion->cliente->nombre)->toBe($cliente->nombre);
});

it('Devolucion → relación detalles devuelve los detalles creados', function () {
    ['cliente' => $cliente, 'producto' => $producto] = setupDevolucion();

    $devolucion = crearDevolucion($cliente->id);

    DetalleDevolucion::create([
        'devolucion_id' => $devolucion->id,
        'producto_id' => $producto->id,
        'cantidad' => 2,
        'precio_unitario' => 100,
        'subtotal' => 200,
        'defectuoso' => false,
    ]);

    expect($devolucion->detalles)->toHaveCount(1);
    expect($devolucion->detalles->first()->producto_id)->toBe($producto->id);
});

// ── SoftDeletes ───────────────────────────────────────────────────────────────

it('Devolucion → softDelete no elimina el registro físicamente', function () {
    ['cliente' => $cliente] = setupDevolucion();

    $devolucion = crearDevolucion($cliente->id);
    $id = $devolucion->id;

    $devolucion->delete();

    expect(Devolucion::find($id))->toBeNull();
    expect(Devolucion::withTrashed()->find($id))->not->toBeNull();
    expect(Devolucion::withTrashed()->find($id)->deleted_at)->not->toBeNull();
});

// ── Confirmar — stock ─────────────────────────────────────────────────────────

it('Devolucion → confirmar restaura stock en bodega existente', function () {
    ['bodega' => $bodega, 'cliente' => $cliente, 'producto' => $producto] = setupDevolucion();

    StockBodega::create(['producto_id' => $producto->id, 'bodega_id' => $bodega->id, 'cantidad' => 5]);

    $venta = crearVentaConfirmada($cliente->id, $bodega->id, ['subtotal' => 200, 'total' => 200, 'saldo_pendiente' => 200]);

    $devolucion = crearDevolucion($cliente->id, [
        'tipo_documento' => 'venta',
        'documento_id' => $venta->id,
        'subtotal' => 200,
        'total' => 200,
    ]);

    DetalleDevolucion::create([
        'devolucion_id' => $devolucion->id,
        'producto_id' => $producto->id,
        'cantidad' => 2,
        'precio_unitario' => 100,
        'subtotal' => 200,
        'defectuoso' => false,
    ]);

    $devolucion->update(['estado' => DevolucionEstado::CONFIRMADA]);

    $stock = StockBodega::where('producto_id', $producto->id)
        ->where('bodega_id', $bodega->id)
        ->value('cantidad');

    expect((float) $stock)->toBe(7.0); // 5 previo + 2 devueltos
});

it('Devolucion → confirmar crea registro de stock si no existía en bodega', function () {
    ['bodega' => $bodega, 'cliente' => $cliente, 'producto' => $producto] = setupDevolucion();

    $venta = crearVentaConfirmada($cliente->id, $bodega->id, ['subtotal' => 150, 'total' => 150, 'saldo_pendiente' => 150]);

    $devolucion = crearDevolucion($cliente->id, [
        'tipo_documento' => 'venta',
        'documento_id' => $venta->id,
        'subtotal' => 150,
        'total' => 150,
    ]);

    DetalleDevolucion::create([
        'devolucion_id' => $devolucion->id,
        'producto_id' => $producto->id,
        'cantidad' => 3,
        'precio_unitario' => 50,
        'subtotal' => 150,
        'defectuoso' => false,
    ]);

    $devolucion->update(['estado' => DevolucionEstado::CONFIRMADA]);

    $stock = StockBodega::where('producto_id', $producto->id)
        ->where('bodega_id', $bodega->id)
        ->value('cantidad');

    expect((float) $stock)->toBe(3.0);
});

it('Devolucion → confirmar con múltiples detalles restaura stock de cada producto', function () {
    ['bodega' => $bodega, 'cliente' => $cliente] = setupDevolucion();

    $prod1 = Producto::create(['codigo' => 'P1-'.uniqid(), 'nombre' => 'Prod 1', 'precio_venta' => 100, 'precio_compra' => 80, 'activo' => true]);
    $prod2 = Producto::create(['codigo' => 'P2-'.uniqid(), 'nombre' => 'Prod 2', 'precio_venta' => 200, 'precio_compra' => 150, 'activo' => true]);

    StockBodega::create(['producto_id' => $prod1->id, 'bodega_id' => $bodega->id, 'cantidad' => 10]);
    StockBodega::create(['producto_id' => $prod2->id, 'bodega_id' => $bodega->id, 'cantidad' => 5]);

    $venta = crearVentaConfirmada($cliente->id, $bodega->id, ['subtotal' => 500, 'total' => 500, 'saldo_pendiente' => 500]);

    $devolucion = crearDevolucion($cliente->id, [
        'tipo_documento' => 'venta',
        'documento_id' => $venta->id,
        'subtotal' => 500,
        'total' => 500,
    ]);

    DetalleDevolucion::create(['devolucion_id' => $devolucion->id, 'producto_id' => $prod1->id, 'cantidad' => 3, 'precio_unitario' => 100, 'subtotal' => 300, 'defectuoso' => false]);
    DetalleDevolucion::create(['devolucion_id' => $devolucion->id, 'producto_id' => $prod2->id, 'cantidad' => 1, 'precio_unitario' => 200, 'subtotal' => 200, 'defectuoso' => true]);

    $devolucion->update(['estado' => DevolucionEstado::CONFIRMADA]);

    expect((float) StockBodega::where('producto_id', $prod1->id)->where('bodega_id', $bodega->id)->value('cantidad'))->toBe(13.0);
    expect((float) StockBodega::where('producto_id', $prod2->id)->where('bodega_id', $bodega->id)->value('cantidad'))->toBe(6.0);
});

// ── Confirmar — MovimientoInventario ─────────────────────────────────────────

it('Devolucion → confirmar registra MovimientoInventario con campos correctos', function () {
    ['bodega' => $bodega, 'cliente' => $cliente, 'producto' => $producto] = setupDevolucion();

    StockBodega::create(['producto_id' => $producto->id, 'bodega_id' => $bodega->id, 'cantidad' => 10]);

    $venta = crearVentaConfirmada($cliente->id, $bodega->id, ['subtotal' => 100, 'total' => 100, 'saldo_pendiente' => 100]);

    $devolucion = crearDevolucion($cliente->id, [
        'tipo_documento' => 'venta',
        'documento_id' => $venta->id,
        'subtotal' => 100,
        'total' => 100,
    ]);

    DetalleDevolucion::create([
        'devolucion_id' => $devolucion->id,
        'producto_id' => $producto->id,
        'cantidad' => 1,
        'precio_unitario' => 100,
        'subtotal' => 100,
        'defectuoso' => false,
    ]);

    $devolucion->update(['estado' => DevolucionEstado::CONFIRMADA]);

    $movimiento = MovimientoInventario::where('documento_tipo', 'devolucion')
        ->where('documento_id', $devolucion->id)
        ->first();

    expect($movimiento)->not->toBeNull();
    expect($movimiento->tipo_movimiento->value)->toBe('entrada_devolucion');
    expect($movimiento->producto_id)->toBe($producto->id);
    expect($movimiento->bodega_id)->toBe($bodega->id);
    expect((float) $movimiento->cantidad)->toBe(1.0);
    expect((float) $movimiento->stock_resultante)->toBe(11.0); // 10 + 1
});

it('Devolucion → confirmar registra un MovimientoInventario por cada detalle', function () {
    ['bodega' => $bodega, 'cliente' => $cliente] = setupDevolucion();

    $prod1 = Producto::create(['codigo' => 'MP1-'.uniqid(), 'nombre' => 'MP1', 'precio_venta' => 100, 'precio_compra' => 80, 'activo' => true]);
    $prod2 = Producto::create(['codigo' => 'MP2-'.uniqid(), 'nombre' => 'MP2', 'precio_venta' => 200, 'precio_compra' => 150, 'activo' => true]);

    StockBodega::create(['producto_id' => $prod1->id, 'bodega_id' => $bodega->id, 'cantidad' => 5]);
    StockBodega::create(['producto_id' => $prod2->id, 'bodega_id' => $bodega->id, 'cantidad' => 5]);

    $venta = crearVentaConfirmada($cliente->id, $bodega->id);

    $devolucion = crearDevolucion($cliente->id, [
        'tipo_documento' => 'venta',
        'documento_id' => $venta->id,
        'total' => 300,
    ]);

    DetalleDevolucion::create(['devolucion_id' => $devolucion->id, 'producto_id' => $prod1->id, 'cantidad' => 2, 'precio_unitario' => 100, 'subtotal' => 200, 'defectuoso' => false]);
    DetalleDevolucion::create(['devolucion_id' => $devolucion->id, 'producto_id' => $prod2->id, 'cantidad' => 1, 'precio_unitario' => 100, 'subtotal' => 100, 'defectuoso' => false]);

    $devolucion->update(['estado' => DevolucionEstado::CONFIRMADA]);

    $movimientos = MovimientoInventario::where('documento_tipo', 'devolucion')
        ->where('documento_id', $devolucion->id)
        ->get();

    expect($movimientos)->toHaveCount(2);
    expect($movimientos->every(fn ($m) => $m->tipo_movimiento->value === 'entrada_devolucion'))->toBeTrue();
});

// ── Confirmar — saldo cliente ─────────────────────────────────────────────────

it('Devolucion → confirmar reduce el saldo del cliente por el total devuelto', function () {
    ['bodega' => $bodega, 'cliente' => $cliente, 'producto' => $producto] = setupDevolucion();

    StockBodega::create(['producto_id' => $producto->id, 'bodega_id' => $bodega->id, 'cantidad' => 5]);

    $venta = crearVentaConfirmada($cliente->id, $bodega->id, ['subtotal' => 200, 'total' => 200, 'saldo_pendiente' => 200]);

    $devolucion = crearDevolucion($cliente->id, [
        'tipo_documento' => 'venta',
        'documento_id' => $venta->id,
        'subtotal' => 200,
        'total' => 200,
    ]);

    DetalleDevolucion::create([
        'devolucion_id' => $devolucion->id,
        'producto_id' => $producto->id,
        'cantidad' => 2,
        'precio_unitario' => 100,
        'subtotal' => 200,
        'defectuoso' => false,
    ]);

    expect((float) $cliente->saldo)->toBe(500.0);

    $devolucion->update(['estado' => DevolucionEstado::CONFIRMADA]);

    expect((float) $cliente->refresh()->saldo)->toBe(300.0); // 500 - 200
});

it('Devolucion → saldo puede quedar negativo (crédito a favor del cliente)', function () {
    ['bodega' => $bodega, 'cliente' => $cliente, 'producto' => $producto] = setupDevolucion();

    StockBodega::create(['producto_id' => $producto->id, 'bodega_id' => $bodega->id, 'cantidad' => 10]);

    $venta = crearVentaConfirmada($cliente->id, $bodega->id, ['subtotal' => 600, 'total' => 600, 'saldo_pendiente' => 600]);

    $devolucion = crearDevolucion($cliente->id, [
        'tipo_documento' => 'venta',
        'documento_id' => $venta->id,
        'subtotal' => 600,
        'total' => 600,
    ]);

    DetalleDevolucion::create([
        'devolucion_id' => $devolucion->id,
        'producto_id' => $producto->id,
        'cantidad' => 6,
        'precio_unitario' => 100,
        'subtotal' => 600,
        'defectuoso' => false,
    ]);

    $devolucion->update(['estado' => DevolucionEstado::CONFIRMADA]);

    expect((float) $cliente->refresh()->saldo)->toBe(-100.0); // 500 - 600 = -100 (crédito)
});

// ── Confirmar — MovimientoSaldoCliente ────────────────────────────────────────

it('Devolucion → confirmar registra MovimientoSaldoCliente con monto negativo', function () {
    ['bodega' => $bodega, 'cliente' => $cliente, 'producto' => $producto] = setupDevolucion();

    StockBodega::create(['producto_id' => $producto->id, 'bodega_id' => $bodega->id, 'cantidad' => 5]);

    $venta = crearVentaConfirmada($cliente->id, $bodega->id, ['subtotal' => 150, 'total' => 150, 'saldo_pendiente' => 150]);

    $devolucion = crearDevolucion($cliente->id, [
        'tipo_documento' => 'venta',
        'documento_id' => $venta->id,
        'subtotal' => 150,
        'total' => 150,
    ]);

    DetalleDevolucion::create([
        'devolucion_id' => $devolucion->id,
        'producto_id' => $producto->id,
        'cantidad' => 1,
        'precio_unitario' => 150,
        'subtotal' => 150,
        'defectuoso' => false,
    ]);

    $devolucion->update(['estado' => DevolucionEstado::CONFIRMADA]);

    $movSaldo = MovimientoSaldoCliente::where('cliente_id', $cliente->id)
        ->where('tipo', 'devolucion')
        ->first();

    expect($movSaldo)->not->toBeNull();
    expect((float) $movSaldo->monto)->toBe(-150.0);
    expect((float) $movSaldo->saldo_anterior)->toBe(500.0);
    expect((float) $movSaldo->saldo_nuevo)->toBe(350.0);
    expect($movSaldo->referencia)->toContain('devolucion_');
});

// ── Confirmar — campo confirmada_en ───────────────────────────────────────────

it('Devolucion → confirmar registra fecha en confirmada_en y cambia estado', function () {
    ['bodega' => $bodega, 'cliente' => $cliente, 'producto' => $producto] = setupDevolucion();

    StockBodega::create(['producto_id' => $producto->id, 'bodega_id' => $bodega->id, 'cantidad' => 5]);

    $venta = crearVentaConfirmada($cliente->id, $bodega->id, ['subtotal' => 100, 'total' => 100, 'saldo_pendiente' => 100]);

    $devolucion = crearDevolucion($cliente->id, [
        'tipo_documento' => 'venta',
        'documento_id' => $venta->id,
        'subtotal' => 100,
        'total' => 100,
    ]);

    DetalleDevolucion::create([
        'devolucion_id' => $devolucion->id,
        'producto_id' => $producto->id,
        'cantidad' => 1,
        'precio_unitario' => 100,
        'subtotal' => 100,
        'defectuoso' => false,
    ]);

    expect($devolucion->confirmada_en)->toBeNull();

    $devolucion->update(['estado' => DevolucionEstado::CONFIRMADA]);

    $fresh = $devolucion->refresh();
    expect($fresh->estado)->toBe(DevolucionEstado::CONFIRMADA);
    expect($fresh->confirmada_en)->not->toBeNull();
});

// ── DetalleDevolucion ─────────────────────────────────────────────────────────

it('DetalleDevolucion → campo defectuoso se castea a boolean', function () {
    ['cliente' => $cliente, 'producto' => $producto] = setupDevolucion();

    $devolucion = crearDevolucion($cliente->id);

    $detalle = DetalleDevolucion::create([
        'devolucion_id' => $devolucion->id,
        'producto_id' => $producto->id,
        'cantidad' => 1,
        'precio_unitario' => 50,
        'subtotal' => 50,
        'defectuoso' => true,
    ]);

    expect($detalle->refresh()->defectuoso)->toBeTrue();
});

it('DetalleDevolucion → relación devolucion() devuelve la devolución correcta', function () {
    ['cliente' => $cliente, 'producto' => $producto] = setupDevolucion();

    $devolucion = crearDevolucion($cliente->id);

    $detalle = DetalleDevolucion::create([
        'devolucion_id' => $devolucion->id,
        'producto_id' => $producto->id,
        'cantidad' => 1,
        'precio_unitario' => 50,
        'subtotal' => 50,
        'defectuoso' => false,
    ]);

    expect($detalle->devolucion->id)->toBe($devolucion->id);
    expect($detalle->devolucion->numero)->toBe($devolucion->numero);
});

it('DetalleDevolucion → relación producto() devuelve el producto correcto', function () {
    ['cliente' => $cliente, 'producto' => $producto] = setupDevolucion();

    $devolucion = crearDevolucion($cliente->id);

    $detalle = DetalleDevolucion::create([
        'devolucion_id' => $devolucion->id,
        'producto_id' => $producto->id,
        'cantidad' => 2,
        'precio_unitario' => 75,
        'subtotal' => 150,
        'defectuoso' => false,
    ]);

    expect($detalle->producto->id)->toBe($producto->id);
    expect($detalle->producto->nombre)->toBe($producto->nombre);
});

// ── MovimientoSaldoCliente — modelo directo ───────────────────────────────────

it('MovimientoSaldoCliente → se puede crear con tipo devolucion y monto negativo', function () {
    ['cliente' => $cliente] = setupDevolucion();

    $mov = MovimientoSaldoCliente::create([
        'cliente_id' => $cliente->id,
        'tipo' => 'devolucion',
        'referencia' => 'devolucion_1',
        'monto' => -100.00,
        'saldo_anterior' => 500.00,
        'saldo_nuevo' => 400.00,
        'descripcion' => 'Test movimiento',
    ]);

    expect($mov->id)->not->toBeNull();
    expect($mov->tipo->value)->toBe('devolucion');
    expect((float) $mov->monto)->toBe(-100.0);
    expect($mov->cliente->id)->toBe($cliente->id);
});

it('MovimientoSaldoCliente → acepta todos los tipos del enum de la migración', function () {
    ['cliente' => $cliente] = setupDevolucion();

    foreach (['compra', 'venta', 'devolucion', 'pago', 'ajuste'] as $i => $tipo) {
        $mov = MovimientoSaldoCliente::create([
            'cliente_id' => $cliente->id,
            'tipo' => $tipo,
            'referencia' => "{$tipo}_{$i}",
            'monto' => 100.00,
            'saldo_anterior' => 0,
            'saldo_nuevo' => 100.00,
        ]);
        expect($mov->tipo->value)->toBe($tipo);
    }
});

// ── Venta sin Numeracion activa — prueba explícita del requisito DIAN ─────────
//
// Documenta POR QUÉ los tests del Contexto A usan withoutEvents():
// Venta::creating() lanza excepción si no hay Numeracion activa para el año.

it('[SIN NUMERACION] Venta::create() lanza excepción cuando no hay Numeracion activa', function () {
    ['bodega' => $bodega, 'cliente' => $cliente] = setupDevolucion();

    // No se crea ninguna Numeracion — la BD está limpia por RefreshDatabase

    expect(fn () => Venta::create([
        'estado' => VentaEstado::BORRADOR,
        'cliente_id' => $cliente->id,
        'bodega_id' => $bodega->id,
        'subtotal' => 100,
        'descuento' => 0,
        'impuestos' => 0,
        'total' => 100,
        'saldo_pendiente' => 100,
        'estado_pago' => 'pendiente',
        'fecha' => now(),
    ]))->toThrow(Exception::class);
});

// ── Contexto B: con Numeracion activa (flujo real completo) ───────────────────
//
// Estos tests validan que el módulo Devolucion funciona correctamente
// cuando la Venta fue creada por el flujo normal (con Numeracion DIAN activa).
// Complementan los tests anteriores que usan withoutEvents() para aislamiento.

it('[CON NUMERACION] Venta::create() genera número automático con Numeracion activa', function () {
    ['bodega' => $bodega, 'cliente' => $cliente] = setupDevolucion();

    crearNumeracionVenta();

    $venta = Venta::create([
        'estado' => VentaEstado::BORRADOR,
        'cliente_id' => $cliente->id,
        'bodega_id' => $bodega->id,
        'subtotal' => 100,
        'descuento' => 0,
        'impuestos' => 0,
        'total' => 100,
        'saldo_pendiente' => 100,
        'estado_pago' => 'pendiente',
        'fecha' => now(),
    ]);

    expect($venta->numero)->toMatch('/^VEN\d+$/');
    expect($venta->id)->not->toBeNull();
});

it('[CON NUMERACION] Devolucion puede crearse para una Venta generada con Numeracion activa', function () {
    ['bodega' => $bodega, 'cliente' => $cliente, 'producto' => $producto] = setupDevolucion();

    crearNumeracionVenta();

    $venta = Venta::create([
        'estado' => VentaEstado::BORRADOR,
        'cliente_id' => $cliente->id,
        'bodega_id' => $bodega->id,
        'subtotal' => 200,
        'descuento' => 0,
        'impuestos' => 0,
        'total' => 200,
        'saldo_pendiente' => 200,
        'estado_pago' => 'pendiente',
        'fecha' => now(),
    ]);

    $devolucion = crearDevolucion($cliente->id, [
        'tipo_documento' => 'venta',
        'documento_id' => $venta->id,
        'subtotal' => 200,
        'total' => 200,
    ]);

    expect($devolucion->numero)->toMatch('/^DVL-\d{5}$/');
    expect($devolucion->documento_id)->toBe($venta->id);
    expect($devolucion->tipo_documento->value)->toBe('venta');
});

it('[CON NUMERACION] confirmar devolución restaura stock y saldo con Venta real', function () {
    ['bodega' => $bodega, 'cliente' => $cliente, 'producto' => $producto] = setupDevolucion();

    StockBodega::create(['producto_id' => $producto->id, 'bodega_id' => $bodega->id, 'cantidad' => 8]);

    crearNumeracionVenta();

    $venta = Venta::create([
        'estado' => VentaEstado::BORRADOR,
        'cliente_id' => $cliente->id,
        'bodega_id' => $bodega->id,
        'subtotal' => 300,
        'descuento' => 0,
        'impuestos' => 0,
        'total' => 300,
        'saldo_pendiente' => 300,
        'estado_pago' => 'pendiente',
        'fecha' => now(),
    ]);

    $devolucion = crearDevolucion($cliente->id, [
        'tipo_documento' => 'venta',
        'documento_id' => $venta->id,
        'subtotal' => 300,
        'total' => 300,
    ]);

    DetalleDevolucion::create([
        'devolucion_id' => $devolucion->id,
        'producto_id' => $producto->id,
        'cantidad' => 3,
        'precio_unitario' => 100,
        'subtotal' => 300,
        'defectuoso' => false,
    ]);

    $devolucion->update(['estado' => DevolucionEstado::CONFIRMADA]);

    // Stock: 8 previos + 3 devueltos = 11
    expect((float) StockBodega::where('producto_id', $producto->id)->value('cantidad'))->toBe(11.0);

    // Saldo: 500 previo - 300 devuelto = 200
    expect((float) $cliente->refresh()->saldo)->toBe(200.0);

    // MovimientoInventario registrado correctamente
    $mov = MovimientoInventario::where('documento_tipo', 'devolucion')
        ->where('documento_id', $devolucion->id)
        ->first();
    expect($mov)->not->toBeNull();
    expect($mov->tipo_movimiento->value)->toBe('entrada_devolucion');
});

it('[CON NUMERACION] dos ventas consecutivas generan números correlativos', function () {
    ['bodega' => $bodega, 'cliente' => $cliente] = setupDevolucion();

    crearNumeracionVenta();

    $datos = [
        'estado' => VentaEstado::BORRADOR,
        'cliente_id' => $cliente->id,
        'bodega_id' => $bodega->id,
        'subtotal' => 100,
        'descuento' => 0,
        'impuestos' => 0,
        'total' => 100,
        'saldo_pendiente' => 100,
        'estado_pago' => 'pendiente',
        'fecha' => now(),
    ];

    $v1 = Venta::create($datos);
    $v2 = Venta::create($datos);

    expect($v1->numero)->toBe('VEN000001');
    expect($v2->numero)->toBe('VEN000002');
});
