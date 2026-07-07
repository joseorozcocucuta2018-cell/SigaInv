<?php

use App\Enums\TrasladoEstado;
use App\Models\Bodega;
use App\Models\Ciudad;
use App\Models\Departamento;
use App\Models\Producto;
use App\Models\StockBodega;
use App\Models\Traslado;
use App\Models\TrasladoDetalle;
use App\Services\TrasladoService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| TrasladoIntegrityTest
|
| Verifica que un traslado conserva el stock total entre bodegas.
| No usa factories — crea todos los modelos directamente.
|--------------------------------------------------------------------------
*/

function setupTrasladoIntegrity(): array
{
    $depto = Departamento::create(['nombre' => 'Depto Traslado']);
    $ciudad = Ciudad::create(['nombre' => 'Ciudad Traslado', 'departamento_id' => $depto->id]);

    $bodega1 = Bodega::create([
        'nombre' => 'Bodega Origen',
        'direccion1' => 'Calle 1',
        'departamento_id' => $depto->id,
        'ciudad_id' => $ciudad->id,
        'activo' => true,
    ]);

    $bodega2 = Bodega::create([
        'nombre' => 'Bodega Destino',
        'direccion1' => 'Calle 2',
        'departamento_id' => $depto->id,
        'ciudad_id' => $ciudad->id,
        'activo' => true,
    ]);

    $producto = Producto::create([
        'codigo' => 'TRI-'.uniqid(),
        'nombre' => 'Producto Traslado',
        'precio_compra' => 80,
        'activo' => true,
    ]);

    return compact('bodega1', 'bodega2', 'producto');
}

it('traslado conserva el stock total entre bodegas', function () {
    ['bodega1' => $origen, 'bodega2' => $destino, 'producto' => $producto] = setupTrasladoIntegrity();

    $stockInicial = 50;
    StockBodega::create([
        'producto_id' => $producto->id,
        'bodega_id' => $origen->id,
        'cantidad' => $stockInicial,
    ]);

    $traslado = Traslado::create([
        'bodega_origen_id' => $origen->id,
        'bodega_destino_id' => $destino->id,
        'estado' => TrasladoEstado::BORRADOR,
        'fecha' => now(),
    ]);

    TrasladoDetalle::create([
        'traslado_id' => $traslado->id,
        'producto_id' => $producto->id,
        'cantidad' => 20,
    ]);

    (new TrasladoService)->confirmar($traslado);

    $stockOrigen = (float) StockBodega::where('producto_id', $producto->id)->where('bodega_id', $origen->id)->value('cantidad');
    $stockDestino = (float) StockBodega::where('producto_id', $producto->id)->where('bodega_id', $destino->id)->value('cantidad');

    expect($stockOrigen + $stockDestino)->toBe((float) $stockInicial);
});

it('traslado parcial deja stock correcto en ambas bodegas', function () {
    ['bodega1' => $origen, 'bodega2' => $destino, 'producto' => $producto] = setupTrasladoIntegrity();

    StockBodega::create(['producto_id' => $producto->id, 'bodega_id' => $origen->id, 'cantidad' => 100]);

    $traslado = Traslado::create([
        'bodega_origen_id' => $origen->id,
        'bodega_destino_id' => $destino->id,
        'estado' => TrasladoEstado::BORRADOR,
        'fecha' => now(),
    ]);

    TrasladoDetalle::create([
        'traslado_id' => $traslado->id,
        'producto_id' => $producto->id,
        'cantidad' => 30,
    ]);

    (new TrasladoService)->confirmar($traslado);

    expect((float) StockBodega::where('producto_id', $producto->id)->where('bodega_id', $origen->id)->value('cantidad'))->toBe(70.0);
    expect((float) StockBodega::where('producto_id', $producto->id)->where('bodega_id', $destino->id)->value('cantidad'))->toBe(30.0);
});
