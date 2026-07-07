<?php

use App\Models\Bodega;
use App\Models\Ciudad;
use App\Models\Departamento;
use App\Models\Producto;
use App\Models\StockBodega;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| StockConsistencyTest
|
| Verifica la consistencia del stock entre bodegas.
| No usa factories — crea todos los modelos directamente.
|--------------------------------------------------------------------------
*/

function setupStockConsistency(): array
{
    $depto = Departamento::create(['nombre' => 'Depto Stock']);
    $ciudad = Ciudad::create(['nombre' => 'Ciudad Stock', 'departamento_id' => $depto->id]);

    $bodega1 = Bodega::create([
        'nombre' => 'Bodega 1',
        'direccion1' => 'Calle 1',
        'departamento_id' => $depto->id,
        'ciudad_id' => $ciudad->id,
    ]);

    $bodega2 = Bodega::create([
        'nombre' => 'Bodega 2',
        'direccion1' => 'Calle 2',
        'departamento_id' => $depto->id,
        'ciudad_id' => $ciudad->id,
    ]);

    $producto = Producto::create([
        'codigo' => 'STK-'.uniqid(),
        'nombre' => 'Producto Stock Test',
        'activo' => true,
    ]);

    return compact('bodega1', 'bodega2', 'producto');
}

it('stock total coincide con suma de bodegas', function () {
    ['bodega1' => $b1, 'bodega2' => $b2, 'producto' => $producto] = setupStockConsistency();

    StockBodega::create(['producto_id' => $producto->id, 'bodega_id' => $b1->id, 'cantidad' => 10]);
    StockBodega::create(['producto_id' => $producto->id, 'bodega_id' => $b2->id, 'cantidad' => 5]);

    $total = StockBodega::where('producto_id', $producto->id)->sum('cantidad');

    expect((int) $total)->toBe(15);
});

it('un producto sin stock en ninguna bodega tiene total 0', function () {
    ['producto' => $producto] = setupStockConsistency();

    $total = StockBodega::where('producto_id', $producto->id)->sum('cantidad');

    expect((int) $total)->toBe(0);
});

it('stock de un producto no incluye stock de otro producto', function () {
    ['bodega1' => $b1, 'producto' => $producto] = setupStockConsistency();

    $otroProducto = Producto::create([
        'codigo' => 'STK2-'.uniqid(),
        'nombre' => 'Otro Producto',
        'activo' => true,
    ]);

    StockBodega::create(['producto_id' => $producto->id,     'bodega_id' => $b1->id, 'cantidad' => 10]);
    StockBodega::create(['producto_id' => $otroProducto->id, 'bodega_id' => $b1->id, 'cantidad' => 99]);

    $total = StockBodega::where('producto_id', $producto->id)->sum('cantidad');

    expect((int) $total)->toBe(10);
});

it('stock puede ser 0 en una bodega sin afectar otras', function () {
    ['bodega1' => $b1, 'bodega2' => $b2, 'producto' => $producto] = setupStockConsistency();

    StockBodega::create(['producto_id' => $producto->id, 'bodega_id' => $b1->id, 'cantidad' => 0]);
    StockBodega::create(['producto_id' => $producto->id, 'bodega_id' => $b2->id, 'cantidad' => 20]);

    $total = StockBodega::where('producto_id', $producto->id)->sum('cantidad');

    expect((int) $total)->toBe(20);
});
