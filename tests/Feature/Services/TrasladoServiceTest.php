<?php

use App\Enums\TrasladoEstado;
use App\Models\Bodega;
use App\Models\Ciudad;
use App\Models\Departamento;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\StockBodega;
use App\Models\Traslado;
use App\Models\TrasladoDetalle;
use App\Services\TrasladoService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| TrasladoServiceTest
|
| Verifica el comportamiento del servicio de traslados entre bodegas:
| - Confirmar descuenta stock de bodega origen
| - Confirmar aumenta stock en bodega destino
| - Confirmar registra movimientos salida_traslado y entrada_traslado
| - Confirmar cambia estado a 'confirmada'
| - No se puede confirmar sin detalles
| - No se puede confirmar con stock insuficiente en origen
| - No se puede confirmar dos veces (estado != borrador)
| - Revertir devuelve stock a bodega origen
| - Revertir registra movimientos reverso_traslado
| - No se puede revertir un traslado en borrador
|--------------------------------------------------------------------------
*/

// ── Helper: setup base ────────────────────────────────────────────────────────
function setupTraslado(): array
{
    $depto = Departamento::create(['nombre' => 'Depto Test']);
    $ciudad = Ciudad::create(['nombre' => 'Ciudad Test', 'departamento_id' => $depto->id]);

    $bodegaOrigen = Bodega::create([
        'nombre' => 'Bodega Origen',
        'direccion1' => 'Calle 1',
        'departamento_id' => $depto->id,
        'ciudad_id' => $ciudad->id,
        'activo' => true,
    ]);

    $bodegaDestino = Bodega::create([
        'nombre' => 'Bodega Destino',
        'direccion1' => 'Calle 2',
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

    // Stock inicial en bodega origen
    StockBodega::create([
        'producto_id' => $producto->id,
        'bodega_id' => $bodegaOrigen->id,
        'cantidad' => 50,
    ]);

    return compact('bodegaOrigen', 'bodegaDestino', 'producto');
}

function crearTraslado(int $origenId, int $destinoId, TrasladoEstado $estado = TrasladoEstado::BORRADOR): Traslado
{
    return Traslado::create([
        'bodega_origen_id' => $origenId,
        'bodega_destino_id' => $destinoId,
        'estado' => $estado,
        'fecha' => now(),
    ]);
}

// ── Confirmar — stock ─────────────────────────────────────────────────────────

it('TrasladoService → confirmar descuenta stock de bodega origen', function () {
    ['bodegaOrigen' => $origen, 'bodegaDestino' => $destino, 'producto' => $producto] = setupTraslado();

    $traslado = crearTraslado($origen->id, $destino->id);
    TrasladoDetalle::create([
        'traslado_id' => $traslado->id,
        'producto_id' => $producto->id,
        'cantidad' => 20,
    ]);

    (new TrasladoService)->confirmar($traslado);

    $stockOrigen = StockBodega::where('producto_id', $producto->id)
        ->where('bodega_id', $origen->id)
        ->value('cantidad');

    expect((float) $stockOrigen)->toBe(30.0);
});

it('TrasladoService → confirmar aumenta stock en bodega destino', function () {
    ['bodegaOrigen' => $origen, 'bodegaDestino' => $destino, 'producto' => $producto] = setupTraslado();

    $traslado = crearTraslado($origen->id, $destino->id);
    TrasladoDetalle::create([
        'traslado_id' => $traslado->id,
        'producto_id' => $producto->id,
        'cantidad' => 15,
    ]);

    (new TrasladoService)->confirmar($traslado);

    $stockDestino = StockBodega::where('producto_id', $producto->id)
        ->where('bodega_id', $destino->id)
        ->value('cantidad');

    expect((float) $stockDestino)->toBe(15.0);
});

it('TrasladoService → confirmar registra movimiento salida_traslado en origen', function () {
    ['bodegaOrigen' => $origen, 'bodegaDestino' => $destino, 'producto' => $producto] = setupTraslado();

    $traslado = crearTraslado($origen->id, $destino->id);
    TrasladoDetalle::create([
        'traslado_id' => $traslado->id,
        'producto_id' => $producto->id,
        'cantidad' => 10,
    ]);

    (new TrasladoService)->confirmar($traslado);

    $movSalida = MovimientoInventario::where('documento_tipo', 'traslado')
        ->where('documento_id', $traslado->id)
        ->where('tipo_movimiento', 'salida_traslado')
        ->where('bodega_id', $origen->id)
        ->first();

    expect($movSalida)->not->toBeNull();
    expect((float) $movSalida->cantidad)->toBe(10.0);
});

it('TrasladoService → confirmar registra movimiento entrada_traslado en destino', function () {
    ['bodegaOrigen' => $origen, 'bodegaDestino' => $destino, 'producto' => $producto] = setupTraslado();

    $traslado = crearTraslado($origen->id, $destino->id);
    TrasladoDetalle::create([
        'traslado_id' => $traslado->id,
        'producto_id' => $producto->id,
        'cantidad' => 10,
    ]);

    (new TrasladoService)->confirmar($traslado);

    $movEntrada = MovimientoInventario::where('documento_tipo', 'traslado')
        ->where('documento_id', $traslado->id)
        ->where('tipo_movimiento', 'entrada_traslado')
        ->where('bodega_id', $destino->id)
        ->first();

    expect($movEntrada)->not->toBeNull();
    expect((float) $movEntrada->cantidad)->toBe(10.0);
});

it('TrasladoService → confirmar cambia estado a confirmada', function () {
    ['bodegaOrigen' => $origen, 'bodegaDestino' => $destino, 'producto' => $producto] = setupTraslado();

    $traslado = crearTraslado($origen->id, $destino->id);
    TrasladoDetalle::create([
        'traslado_id' => $traslado->id,
        'producto_id' => $producto->id,
        'cantidad' => 5,
    ]);

    (new TrasladoService)->confirmar($traslado);

    expect($traslado->refresh()->estado)->toBe(TrasladoEstado::CONFIRMADA);
});

it('TrasladoService → stock total se conserva (origen + destino = inicial)', function () {
    ['bodegaOrigen' => $origen, 'bodegaDestino' => $destino, 'producto' => $producto] = setupTraslado();

    $stockInicial = 50;
    $cantidadTraslado = 20;

    $traslado = crearTraslado($origen->id, $destino->id);
    TrasladoDetalle::create([
        'traslado_id' => $traslado->id,
        'producto_id' => $producto->id,
        'cantidad' => $cantidadTraslado,
    ]);

    (new TrasladoService)->confirmar($traslado);

    $stockOrigen = (float) StockBodega::where('producto_id', $producto->id)->where('bodega_id', $origen->id)->value('cantidad');
    $stockDestino = (float) StockBodega::where('producto_id', $producto->id)->where('bodega_id', $destino->id)->value('cantidad');

    expect($stockOrigen + $stockDestino)->toBe((float) $stockInicial);
});

// ── Validaciones al confirmar ─────────────────────────────────────────────────

it('TrasladoService → no se puede confirmar sin detalles', function () {
    ['bodegaOrigen' => $origen, 'bodegaDestino' => $destino] = setupTraslado();

    $traslado = crearTraslado($origen->id, $destino->id);

    expect(fn () => (new TrasladoService)->confirmar($traslado))
        ->toThrow(Exception::class, 'no puede ser confirmado');
});

it('TrasladoService → no se puede confirmar con stock insuficiente en origen', function () {
    ['bodegaOrigen' => $origen, 'bodegaDestino' => $destino, 'producto' => $producto] = setupTraslado();

    $traslado = crearTraslado($origen->id, $destino->id);
    TrasladoDetalle::create([
        'traslado_id' => $traslado->id,
        'producto_id' => $producto->id,
        'cantidad' => 100, // más que los 50 disponibles
    ]);

    expect(fn () => (new TrasladoService)->confirmar($traslado))
        ->toThrow(Exception::class, 'Stock insuficiente');
});

it('TrasladoService → no se puede confirmar un traslado ya confirmado', function () {
    ['bodegaOrigen' => $origen, 'bodegaDestino' => $destino, 'producto' => $producto] = setupTraslado();

    $traslado = crearTraslado($origen->id, $destino->id, TrasladoEstado::CONFIRMADA);
    TrasladoDetalle::create([
        'traslado_id' => $traslado->id,
        'producto_id' => $producto->id,
        'cantidad' => 5,
    ]);

    expect(fn () => (new TrasladoService)->confirmar($traslado))
        ->toThrow(Exception::class, 'no puede ser confirmado');
});

// ── Revertir ──────────────────────────────────────────────────────────────────

it('TrasladoService → revertir devuelve stock a bodega origen', function () {
    ['bodegaOrigen' => $origen, 'bodegaDestino' => $destino, 'producto' => $producto] = setupTraslado();

    $traslado = crearTraslado($origen->id, $destino->id);
    TrasladoDetalle::create([
        'traslado_id' => $traslado->id,
        'producto_id' => $producto->id,
        'cantidad' => 20,
    ]);

    (new TrasladoService)->confirmar($traslado);

    // Después de confirmar: origen=30, destino=20
    expect((float) StockBodega::where('producto_id', $producto->id)->where('bodega_id', $origen->id)->value('cantidad'))->toBe(30.0);

    (new TrasladoService)->revertir($traslado->refresh());

    // Después de revertir: origen=50, destino=0
    expect((float) StockBodega::where('producto_id', $producto->id)->where('bodega_id', $origen->id)->value('cantidad'))->toBe(50.0);
    expect((float) StockBodega::where('producto_id', $producto->id)->where('bodega_id', $destino->id)->value('cantidad'))->toBe(0.0);
});

it('TrasladoService → revertir registra movimientos de tipo reverso_traslado', function () {
    ['bodegaOrigen' => $origen, 'bodegaDestino' => $destino, 'producto' => $producto] = setupTraslado();

    $traslado = crearTraslado($origen->id, $destino->id);
    TrasladoDetalle::create([
        'traslado_id' => $traslado->id,
        'producto_id' => $producto->id,
        'cantidad' => 10,
    ]);

    (new TrasladoService)->confirmar($traslado);
    (new TrasladoService)->revertir($traslado->refresh());

    $reversos = MovimientoInventario::where('documento_tipo', 'traslado')
        ->where('documento_id', $traslado->id)
        ->where('tipo_movimiento', 'reverso_traslado')
        ->count();

    expect($reversos)->toBe(2); // uno en cada bodega
});

it('TrasladoService → revertir cambia estado a revertida', function () {
    ['bodegaOrigen' => $origen, 'bodegaDestino' => $destino, 'producto' => $producto] = setupTraslado();

    $traslado = crearTraslado($origen->id, $destino->id);
    TrasladoDetalle::create([
        'traslado_id' => $traslado->id,
        'producto_id' => $producto->id,
        'cantidad' => 5,
    ]);

    (new TrasladoService)->confirmar($traslado);
    (new TrasladoService)->revertir($traslado->refresh());

    expect($traslado->refresh()->estado)->toBe(TrasladoEstado::REVERTIDA);
});

it('TrasladoService → no se puede revertir un traslado en borrador', function () {
    ['bodegaOrigen' => $origen, 'bodegaDestino' => $destino, 'producto' => $producto] = setupTraslado();

    $traslado = crearTraslado($origen->id, $destino->id, TrasladoEstado::BORRADOR);
    TrasladoDetalle::create([
        'traslado_id' => $traslado->id,
        'producto_id' => $producto->id,
        'cantidad' => 5,
    ]);

    expect(fn () => (new TrasladoService)->revertir($traslado))
        ->toThrow(Exception::class, 'no puede ser revertido');
});
