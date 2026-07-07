<?php

use App\Enums\CompraEstado;
use App\Models\Bodega;
use App\Models\Ciudad;
use App\Models\Compra;
use App\Models\Departamento;
use App\Models\DetalleCompra;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\StockBodega;
use App\Services\CompraService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| CompraServiceTest
|
| Verifica el comportamiento del servicio de compras:
| - Confirmar aumenta stock
| - Confirmar registra movimiento entrada_compra
| - Confirmar actualiza costo_promedio del producto (CPP)
| - Anular compra confirmada revierte el stock
| - Anular registra movimiento reverso_anulacion
| - No se puede registrar sin detalles
| - No se puede registrar dos veces (idempotencia)
| - No se puede anular una compra ya anulada
|--------------------------------------------------------------------------
*/

// ── Helper: setup base ────────────────────────────────────────────────────────
function setupCompra(): array
{
    $depto = Departamento::create(['nombre' => 'Depto Test']);
    $ciudad = Ciudad::create(['nombre' => 'Ciudad Test', 'departamento_id' => $depto->id]);

    $bodega = Bodega::create([
        'nombre' => 'Bodega Test',
        'direccion1' => 'Calle 1',
        'departamento_id' => $depto->id,
        'ciudad_id' => $ciudad->id,
        'activo' => true,
    ]);

    $proveedor = Proveedor::create([
        'nombre' => 'Proveedor Test',
        'documento' => 'PROV-'.uniqid(),
        'tipo_documento' => 'NIT',
        'telefono' => '123',
        'email' => 'prov-'.uniqid().'@test.com',
        'direccion1' => 'Calle 1',
        'departamento_id' => $depto->id,
        'ciudad_id' => $ciudad->id,
    ]);

    $producto = Producto::create([
        'codigo' => 'COD-'.uniqid(),
        'nombre' => 'Producto Test',
        'precio_compra' => 100,
        'precio_venta' => 150,
        'costo_promedio' => null,
        'activo' => true,
    ]);

    return compact('bodega', 'proveedor', 'producto');
}

function crearCompra(array $data, int $bodegaId, int $proveedorId): Compra
{
    return Compra::create(array_merge([
        'numero' => 'COM-'.uniqid(),
        'estado' => CompraEstado::BORRADOR,
        'proveedor_id' => $proveedorId,
        'bodega_id' => $bodegaId,
        'subtotal' => 0,
        'descuento' => 0,
        'impuestos' => 0,
        'total' => 0,
        'saldo_pendiente' => 0,
        'fecha' => now(),
    ], $data));
}

// ── Confirmar — stock ─────────────────────────────────────────────────────────

it('CompraService → registrar aumenta el stock del producto', function () {
    ['bodega' => $bodega, 'proveedor' => $proveedor, 'producto' => $producto] = setupCompra();

    $compra = crearCompra(['subtotal' => 500, 'total' => 500, 'saldo_pendiente' => 500], $bodega->id, $proveedor->id);

    DetalleCompra::create([
        'compra_id' => $compra->id,
        'producto_id' => $producto->id,
        'cantidad' => 10,
        'precio_unitario' => 50,
    ]);

    CompraService::registrar($compra);

    $stock = StockBodega::where('producto_id', $producto->id)
        ->where('bodega_id', $bodega->id)
        ->value('cantidad');

    expect((float) $stock)->toBe(10.0);
});

it('CompraService → registrar registra movimiento de tipo entrada_compra', function () {
    ['bodega' => $bodega, 'proveedor' => $proveedor, 'producto' => $producto] = setupCompra();

    $compra = crearCompra(['subtotal' => 500, 'total' => 500, 'saldo_pendiente' => 500], $bodega->id, $proveedor->id);

    DetalleCompra::create([
        'compra_id' => $compra->id,
        'producto_id' => $producto->id,
        'cantidad' => 5,
        'precio_unitario' => 100,
    ]);

    CompraService::registrar($compra);

    $movimiento = MovimientoInventario::where('documento_tipo', 'compra')
        ->where('documento_id', $compra->id)
        ->first();

    expect($movimiento)->not->toBeNull();
    expect($movimiento->tipo_movimiento->value)->toBe('entrada_compra');
    expect((float) $movimiento->cantidad)->toBe(5.0);
});

it('CompraService → registrar cambia el estado a REGISTRADA', function () {
    ['bodega' => $bodega, 'proveedor' => $proveedor, 'producto' => $producto] = setupCompra();

    $compra = crearCompra(['subtotal' => 200, 'total' => 200, 'saldo_pendiente' => 200], $bodega->id, $proveedor->id);

    DetalleCompra::create([
        'compra_id' => $compra->id,
        'producto_id' => $producto->id,
        'cantidad' => 2,
        'precio_unitario' => 100,
    ]);

    CompraService::registrar($compra);

    expect($compra->refresh()->estado)->toBe(CompraEstado::REGISTRADA);
});

// ── CostoPromedioService — integrado con CompraService ───────────────────────

it('CompraService → registrar calcula costo promedio ponderado cuando no había stock previo', function () {
    ['bodega' => $bodega, 'proveedor' => $proveedor, 'producto' => $producto] = setupCompra();

    // Sin stock previo — el CPP debe ser el costo de la compra
    $compra = crearCompra(['subtotal' => 1000, 'total' => 1000, 'saldo_pendiente' => 1000], $bodega->id, $proveedor->id);

    DetalleCompra::create([
        'compra_id' => $compra->id,
        'producto_id' => $producto->id,
        'cantidad' => 10,
        'precio_unitario' => 100,
    ]);

    CompraService::registrar($compra);

    // CPP = (0 * costo_anterior + 10 * 100) / (0 + 10) = 100
    expect((float) $producto->refresh()->costo_promedio)->toBe(100.0);
});

it('CompraService → registrar recalcula costo promedio ponderado con stock previo', function () {
    ['bodega' => $bodega, 'proveedor' => $proveedor, 'producto' => $producto] = setupCompra();

    // Stock previo: 10 unidades a $100
    $producto->update(['costo_promedio' => 100]);
    StockBodega::create(['producto_id' => $producto->id, 'bodega_id' => $bodega->id, 'cantidad' => 10]);

    // Nueva compra: 10 unidades a $200
    $compra = crearCompra(['subtotal' => 2000, 'total' => 2000, 'saldo_pendiente' => 2000], $bodega->id, $proveedor->id);

    DetalleCompra::create([
        'compra_id' => $compra->id,
        'producto_id' => $producto->id,
        'cantidad' => 10,
        'precio_unitario' => 200,
    ]);

    CompraService::registrar($compra);

    // CPP = (10*100 + 10*200) / 20 = 3000/20 = 150
    expect((float) $producto->refresh()->costo_promedio)->toBe(150.0);
});

// ── Anular ────────────────────────────────────────────────────────────────────

it('CompraService → anular una compra confirmada revierte el stock', function () {
    ['bodega' => $bodega, 'proveedor' => $proveedor, 'producto' => $producto] = setupCompra();

    $compra = crearCompra(['subtotal' => 500, 'total' => 500, 'saldo_pendiente' => 500], $bodega->id, $proveedor->id);

    DetalleCompra::create([
        'compra_id' => $compra->id,
        'producto_id' => $producto->id,
        'cantidad' => 10,
        'precio_unitario' => 50,
    ]);

    CompraService::registrar($compra);

    // Stock debe ser 10
    expect((float) StockBodega::where('producto_id', $producto->id)->value('cantidad'))->toBe(10.0);

    CompraService::anular($compra->refresh());

    // Stock debe volver a 0
    expect((float) StockBodega::where('producto_id', $producto->id)->value('cantidad'))->toBe(0.0);
});

it('CompraService → anular registra movimiento de tipo reverso_anulacion', function () {
    ['bodega' => $bodega, 'proveedor' => $proveedor, 'producto' => $producto] = setupCompra();

    $compra = crearCompra(['subtotal' => 300, 'total' => 300, 'saldo_pendiente' => 300], $bodega->id, $proveedor->id);

    DetalleCompra::create([
        'compra_id' => $compra->id,
        'producto_id' => $producto->id,
        'cantidad' => 3,
        'precio_unitario' => 100,
    ]);

    CompraService::registrar($compra);
    CompraService::anular($compra->refresh());

    $reverso = MovimientoInventario::where('documento_tipo', 'compra')
        ->where('documento_id', $compra->id)
        ->where('tipo_movimiento', 'reverso_anulacion')
        ->first();

    expect($reverso)->not->toBeNull();
});

it('CompraService → anular cambia el estado a ANULADA', function () {
    ['bodega' => $bodega, 'proveedor' => $proveedor, 'producto' => $producto] = setupCompra();

    $compra = crearCompra(['subtotal' => 100, 'total' => 100, 'saldo_pendiente' => 100], $bodega->id, $proveedor->id);

    DetalleCompra::create([
        'compra_id' => $compra->id,
        'producto_id' => $producto->id,
        'cantidad' => 1,
        'precio_unitario' => 100,
    ]);

    CompraService::registrar($compra);
    CompraService::anular($compra->refresh());

    expect($compra->refresh()->estado)->toBe(CompraEstado::ANULADA);
});

// ── Validaciones / idempotencia ───────────────────────────────────────────────

it('CompraService → no se puede registrar una compra sin detalles', function () {
    ['bodega' => $bodega, 'proveedor' => $proveedor] = setupCompra();

    $compra = crearCompra([], $bodega->id, $proveedor->id);

    expect(fn () => CompraService::registrar($compra))
        ->toThrow(InvalidArgumentException::class);
});

it('CompraService → no se puede registrar dos veces', function () {
    ['bodega' => $bodega, 'proveedor' => $proveedor, 'producto' => $producto] = setupCompra();

    $compra = crearCompra(['subtotal' => 100, 'total' => 100, 'saldo_pendiente' => 100], $bodega->id, $proveedor->id);

    DetalleCompra::create([
        'compra_id' => $compra->id,
        'producto_id' => $producto->id,
        'cantidad' => 1,
        'precio_unitario' => 100,
    ]);

    CompraService::registrar($compra);

    expect(fn () => CompraService::registrar($compra->refresh()))
        ->toThrow(InvalidArgumentException::class, 'Transición no permitida');
});

it('CompraService → no se puede anular una compra ya anulada', function () {
    ['bodega' => $bodega, 'proveedor' => $proveedor, 'producto' => $producto] = setupCompra();

    $compra = crearCompra(['subtotal' => 100, 'total' => 100, 'saldo_pendiente' => 100], $bodega->id, $proveedor->id);

    DetalleCompra::create([
        'compra_id' => $compra->id,
        'producto_id' => $producto->id,
        'cantidad' => 1,
        'precio_unitario' => 100,
    ]);

    CompraService::registrar($compra);
    CompraService::anular($compra->refresh());

    expect(fn () => CompraService::anular($compra->refresh()))
        ->toThrow(InvalidArgumentException::class, 'Transición no permitida');
});
