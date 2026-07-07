<?php

use App\Enums\AjusteEstado;
use App\Enums\MotivoAjuste;
use App\Models\AjusteInventario;
use App\Models\Bodega;
use App\Models\Ciudad;
use App\Models\Departamento;
use App\Models\DetalleAjusteInventario;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\StockBodega;
use App\Services\AjusteInventarioService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| AjusteInventarioServiceTest
|
| Verifica el comportamiento del servicio de ajustes de inventario:
| - Confirmar ajuste positivo incrementa stock
| - Confirmar ajuste negativo decrementa stock
| - Confirmar registra movimientos ajuste_positivo / ajuste_negativo
| - Confirmar cambia estado a confirmado y registra fecha
| - No se puede confirmar un ajuste ya confirmado
| - No se puede confirmar sin diferencias
| - Anular revierte los movimientos de stock
| - Anular cambia estado a anulado
| - No se puede anular un ajuste en borrador
| - Múltiples detalles se procesan correctamente
|--------------------------------------------------------------------------
*/

// ── Helper ──────────────────────────────────────────────────────────────────────
function setupAjuste(): array
{
    $depto = Departamento::create(['nombre' => 'Depto Test']);
    $ciudad = Ciudad::create(['nombre' => 'Ciudad Test', 'departamento_id' => $depto->id]);

    $bodega = Bodega::create([
        'nombre' => 'Bodega Principal',
        'direccion1' => 'Calle 1',
        'departamento_id' => $depto->id,
        'ciudad_id' => $ciudad->id,
        'activo' => true,
    ]);

    $producto = Producto::create([
        'codigo' => 'COD-'.uniqid(),
        'nombre' => 'Producto Test',
        'precio_venta' => 100,
        'precio_compra' => 80,
        'activo' => true,
    ]);

    StockBodega::create([
        'producto_id' => $producto->id,
        'bodega_id' => $bodega->id,
        'cantidad' => 50,
    ]);

    return compact('bodega', 'producto');
}

function crearAjuste(int $bodegaId, AjusteEstado $estado = AjusteEstado::BORRADOR): AjusteInventario
{
    return AjusteInventario::create([
        'bodega_id' => $bodegaId,
        'fecha' => now(),
        'motivo' => MotivoAjuste::CONTEO_FISICO,
        'estado' => $estado,
    ]);
}

// ── Confirmar — ajuste positivo ─────────────────────────────────────────────────

it('AjusteInventarioService → confirmar ajuste positivo incrementa stock', function () {
    ['bodega' => $bodega, 'producto' => $producto] = setupAjuste();

    $ajuste = crearAjuste($bodega->id);
    DetalleAjusteInventario::create([
        'ajuste_inventario_id' => $ajuste->id,
        'producto_id' => $producto->id,
        'stock_sistema' => 50,
        'stock_fisico' => 60,
        'diferencia' => 10,
        'costo_unitario' => 80,
    ]);

    (new AjusteInventarioService)->confirmar($ajuste);

    $stock = StockBodega::where('producto_id', $producto->id)
        ->where('bodega_id', $bodega->id)
        ->value('cantidad');

    expect((float) $stock)->toBe(60.0);
});

// ── Confirmar — ajuste negativo ─────────────────────────────────────────────────

it('AjusteInventarioService → confirmar ajuste negativo decrementa stock', function () {
    ['bodega' => $bodega, 'producto' => $producto] = setupAjuste();

    $ajuste = crearAjuste($bodega->id);
    DetalleAjusteInventario::create([
        'ajuste_inventario_id' => $ajuste->id,
        'producto_id' => $producto->id,
        'stock_sistema' => 50,
        'stock_fisico' => 45,
        'diferencia' => -5,
        'costo_unitario' => 80,
    ]);

    (new AjusteInventarioService)->confirmar($ajuste);

    $stock = StockBodega::where('producto_id', $producto->id)
        ->where('bodega_id', $bodega->id)
        ->value('cantidad');

    expect((float) $stock)->toBe(45.0);
});

// ── Confirmar — movimientos de inventario ───────────────────────────────────────

it('AjusteInventarioService → confirmar registra movimiento ajuste_conteo positivo', function () {
    ['bodega' => $bodega, 'producto' => $producto] = setupAjuste();

    $ajuste = crearAjuste($bodega->id);
    DetalleAjusteInventario::create([
        'ajuste_inventario_id' => $ajuste->id,
        'producto_id' => $producto->id,
        'stock_sistema' => 50,
        'stock_fisico' => 55,
        'diferencia' => 5,
        'costo_unitario' => 80,
    ]);

    (new AjusteInventarioService)->confirmar($ajuste);

    $mov = MovimientoInventario::where('documento_tipo', 'ajuste_inventario')
        ->where('documento_id', $ajuste->id)
        ->where('tipo_movimiento', 'ajuste_conteo')
        ->first();

    expect($mov)->not->toBeNull();
    expect((float) $mov->cantidad)->toBe(5.0);
    expect((float) $mov->stock_resultante)->toBe(55.0);
});

it('AjusteInventarioService → confirmar registra movimiento ajuste_conteo negativo', function () {
    ['bodega' => $bodega, 'producto' => $producto] = setupAjuste();

    $ajuste = crearAjuste($bodega->id);
    DetalleAjusteInventario::create([
        'ajuste_inventario_id' => $ajuste->id,
        'producto_id' => $producto->id,
        'stock_sistema' => 50,
        'stock_fisico' => 42,
        'diferencia' => -8,
        'costo_unitario' => 80,
    ]);

    (new AjusteInventarioService)->confirmar($ajuste);

    $mov = MovimientoInventario::where('documento_tipo', 'ajuste_inventario')
        ->where('documento_id', $ajuste->id)
        ->where('tipo_movimiento', 'ajuste_conteo')
        ->first();

    expect($mov)->not->toBeNull();
    expect((float) $mov->cantidad)->toBe(8.0);
    expect((float) $mov->stock_resultante)->toBe(42.0);
});

// ── Confirmar — estado ──────────────────────────────────────────────────────────

it('AjusteInventarioService → confirmar cambia estado a confirmado', function () {
    ['bodega' => $bodega, 'producto' => $producto] = setupAjuste();

    $ajuste = crearAjuste($bodega->id);
    DetalleAjusteInventario::create([
        'ajuste_inventario_id' => $ajuste->id,
        'producto_id' => $producto->id,
        'stock_sistema' => 50,
        'stock_fisico' => 55,
        'diferencia' => 5,
        'costo_unitario' => 80,
    ]);

    (new AjusteInventarioService)->confirmar($ajuste);

    $ajuste->refresh();
    expect($ajuste->estado)->toBe(AjusteEstado::CONFIRMADO);
    expect($ajuste->confirmado_en)->not->toBeNull();
});

// ── Validaciones al confirmar ───────────────────────────────────────────────────

it('AjusteInventarioService → no se puede confirmar un ajuste ya confirmado', function () {
    ['bodega' => $bodega, 'producto' => $producto] = setupAjuste();

    $ajuste = crearAjuste($bodega->id, AjusteEstado::CONFIRMADO);
    DetalleAjusteInventario::create([
        'ajuste_inventario_id' => $ajuste->id,
        'producto_id' => $producto->id,
        'stock_sistema' => 50,
        'stock_fisico' => 55,
        'diferencia' => 5,
        'costo_unitario' => 80,
    ]);

    expect(fn () => (new AjusteInventarioService)->confirmar($ajuste))
        ->toThrow(InvalidArgumentException::class, 'borrador');
});

it('AjusteInventarioService → no se puede confirmar sin diferencias', function () {
    ['bodega' => $bodega, 'producto' => $producto] = setupAjuste();

    $ajuste = crearAjuste($bodega->id);
    DetalleAjusteInventario::create([
        'ajuste_inventario_id' => $ajuste->id,
        'producto_id' => $producto->id,
        'stock_sistema' => 50,
        'stock_fisico' => 0,
        'diferencia' => 0,
        'costo_unitario' => 80,
    ]);

    expect(fn () => (new AjusteInventarioService)->confirmar($ajuste))
        ->toThrow(InvalidArgumentException::class, 'diferencias');
});

// ── Anular ──────────────────────────────────────────────────────────────────────

it('AjusteInventarioService → anular revierte stock positivo', function () {
    ['bodega' => $bodega, 'producto' => $producto] = setupAjuste();

    $ajuste = crearAjuste($bodega->id);
    DetalleAjusteInventario::create([
        'ajuste_inventario_id' => $ajuste->id,
        'producto_id' => $producto->id,
        'stock_sistema' => 50,
        'stock_fisico' => 60,
        'diferencia' => 10,
        'costo_unitario' => 80,
    ]);

    $service = new AjusteInventarioService;
    $service->confirmar($ajuste);

    // Stock ahora es 60
    expect((float) StockBodega::where('producto_id', $producto->id)->where('bodega_id', $bodega->id)->value('cantidad'))->toBe(60.0);

    $service->anular($ajuste->refresh());

    // Stock regresa a 50
    expect((float) StockBodega::where('producto_id', $producto->id)->where('bodega_id', $bodega->id)->value('cantidad'))->toBe(50.0);
});

it('AjusteInventarioService → anular revierte stock negativo', function () {
    ['bodega' => $bodega, 'producto' => $producto] = setupAjuste();

    $ajuste = crearAjuste($bodega->id);
    DetalleAjusteInventario::create([
        'ajuste_inventario_id' => $ajuste->id,
        'producto_id' => $producto->id,
        'stock_sistema' => 50,
        'stock_fisico' => 45,
        'diferencia' => -5,
        'costo_unitario' => 80,
    ]);

    $service = new AjusteInventarioService;
    $service->confirmar($ajuste);
    expect((float) StockBodega::where('producto_id', $producto->id)->where('bodega_id', $bodega->id)->value('cantidad'))->toBe(45.0);

    $service->anular($ajuste->refresh());
    expect((float) StockBodega::where('producto_id', $producto->id)->where('bodega_id', $bodega->id)->value('cantidad'))->toBe(50.0);
});

it('AjusteInventarioService → anular cambia estado a anulado', function () {
    ['bodega' => $bodega, 'producto' => $producto] = setupAjuste();

    $ajuste = crearAjuste($bodega->id);
    DetalleAjusteInventario::create([
        'ajuste_inventario_id' => $ajuste->id,
        'producto_id' => $producto->id,
        'stock_sistema' => 50,
        'stock_fisico' => 55,
        'diferencia' => 5,
        'costo_unitario' => 80,
    ]);

    $service = new AjusteInventarioService;
    $service->confirmar($ajuste);
    $service->anular($ajuste->refresh());

    expect($ajuste->refresh()->estado)->toBe(AjusteEstado::ANULADO);
});

it('AjusteInventarioService → no se puede anular un ajuste en borrador', function () {
    ['bodega' => $bodega] = setupAjuste();

    $ajuste = crearAjuste($bodega->id);

    expect(fn () => (new AjusteInventarioService)->anular($ajuste))
        ->toThrow(InvalidArgumentException::class, 'confirmados');
});

// ── Múltiples detalles ──────────────────────────────────────────────────────────

it('AjusteInventarioService → confirmar procesa múltiples detalles correctamente', function () {
    ['bodega' => $bodega, 'producto' => $producto] = setupAjuste();

    $producto2 = Producto::create([
        'codigo' => 'COD-'.uniqid(),
        'nombre' => 'Producto Test 2',
        'precio_venta' => 200,
        'precio_compra' => 150,
        'activo' => true,
    ]);
    StockBodega::create([
        'producto_id' => $producto2->id,
        'bodega_id' => $bodega->id,
        'cantidad' => 30,
    ]);

    $ajuste = crearAjuste($bodega->id);

    // Producto 1: +10 (50→60)
    DetalleAjusteInventario::create([
        'ajuste_inventario_id' => $ajuste->id,
        'producto_id' => $producto->id,
        'stock_sistema' => 50,
        'stock_fisico' => 60,
        'diferencia' => 10,
        'costo_unitario' => 80,
    ]);

    // Producto 2: -5 (30→25)
    DetalleAjusteInventario::create([
        'ajuste_inventario_id' => $ajuste->id,
        'producto_id' => $producto2->id,
        'stock_sistema' => 30,
        'stock_fisico' => 25,
        'diferencia' => -5,
        'costo_unitario' => 150,
    ]);

    // Detalle sin diferencia (no debe generar movimiento)
    DetalleAjusteInventario::create([
        'ajuste_inventario_id' => $ajuste->id,
        'producto_id' => $producto->id,
        'stock_sistema' => 60,
        'stock_fisico' => 0,
        'diferencia' => 0,
        'costo_unitario' => 80,
    ]);

    (new AjusteInventarioService)->confirmar($ajuste);

    expect((float) StockBodega::where('producto_id', $producto->id)->where('bodega_id', $bodega->id)->value('cantidad'))->toBe(60.0);
    expect((float) StockBodega::where('producto_id', $producto2->id)->where('bodega_id', $bodega->id)->value('cantidad'))->toBe(25.0);

    // Solo 2 movimientos (el de diferencia 0 no cuenta)
    $movimientos = MovimientoInventario::where('documento_tipo', 'ajuste_inventario')
        ->where('documento_id', $ajuste->id)
        ->count();
    expect($movimientos)->toBe(2);
});

it('AjusteInventarioService → numero se genera automáticamente', function () {
    ['bodega' => $bodega] = setupAjuste();

    $ajuste1 = crearAjuste($bodega->id);
    $ajuste2 = crearAjuste($bodega->id);

    expect($ajuste1->numero)->toStartWith('AJU-');
    expect($ajuste2->numero)->toStartWith('AJU-');
    expect($ajuste1->numero)->not->toBe($ajuste2->numero);
});
