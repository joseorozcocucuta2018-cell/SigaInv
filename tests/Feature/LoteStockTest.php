<?php

use App\Enums\VentaEstado;
use App\Models\Bodega;
use App\Models\Ciudad;
use App\Models\Cliente;
use App\Models\Departamento;
use App\Models\DetalleVenta;
use App\Models\Numeracion;
use App\Models\Producto;
use App\Models\StockBodega;
use App\Models\StockBodegaLote;
use App\Models\Venta;
use App\Services\StockService;
use App\Services\VentaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('venta con lote decrementa stock por lote al confirmar', function () {
    Numeracion::create([
        'tipo_documento' => 'venta',
        'prefijo' => 'VEN',
        'consecutivo_desde' => 1,
        'consecutivo_hasta' => 9999,
        'consecutivo_actual' => 0,
        'anno' => now()->year,
        'resolucion_numero' => 'TEST-00001',
    ]);

    $departamento = Departamento::create(['nombre' => 'D1']);
    $ciudad = Ciudad::create(['nombre' => 'C1', 'departamento_id' => $departamento->id]);

    $bodega = Bodega::create([
        'nombre' => 'B1',
        'direccion1' => 'Calle 123',
        'departamento_id' => $departamento->id,
        'ciudad_id' => $ciudad->id,
        'activo' => true,
    ]);
    $producto = Producto::create([
        'nombre' => 'Artículo',
        'codigo' => 'P001',
        'precio_venta' => 100,
        'activo' => true,
        'exige_lote' => true,
    ]);

    $stock = StockBodega::create([
        'producto_id' => $producto->id,
        'bodega_id' => $bodega->id,
        'cantidad' => 0,
    ]);
    $lote = StockBodegaLote::create([
        'stock_bodega_id' => $stock->id,
        'lote' => 'ABC123',
        'fecha_vencimiento' => now()->addDays(30),
        'cantidad' => 10,
    ]);

    expect(StockService::getAvailableStock(
        $producto->id,
        $bodega->id,
        'ABC123',
        $lote->fecha_vencimiento->toDateString()
    ))->toEqual(10);

    $cliente = Cliente::forceCreate([
        'nombre' => 'Cliente 1',
        'documento' => '000',
        'telefono' => '000000',
        'email' => 'test@example.com',
        'direccion1' => 'Calle 1',
        'departamento_id' => $departamento->id,
        'ciudad_id' => $ciudad->id,
    ]);

    $venta = Venta::create([
        'cliente_id' => $cliente->id,
        'bodega_id' => $bodega->id,
        'subtotal' => 15,
        'descuento' => 0,
        'impuestos' => 0,
        'total' => 15,
        'saldo_pendiente' => 15,
        'estado_pago' => 'pendiente',
        'estado' => VentaEstado::BORRADOR,
    ]);

    DetalleVenta::create([
        'venta_id' => $venta->id,
        'producto_id' => $producto->id,
        'cantidad' => 3,
        'precio_unitario' => 5,
        'subtotal' => 15,
        'lote' => 'ABC123',
        'fecha_vencimiento' => now()->addDays(30)->toDateString(),
    ]);

    // El stock NO se debe afectar mientras está en borrador
    expect((float) $lote->refresh()->cantidad)->toEqual(10.0);

    // Al confirmar sí debe descontar
    VentaService::confirmar($venta);

    expect((float) $lote->refresh()->cantidad)->toEqual(7.0);

    $exists = DB::table('movimientos_inventario')
        ->where('producto_id', $producto->id)
        ->where('tipo_movimiento', 'salida_venta')
        ->where('lote', 'ABC123')
        ->exists();
    expect($exists)->toBeTrue();
});

it('venta lote insuficiente lanza excepcion al confirmar', function () {
    Numeracion::create([
        'tipo_documento' => 'venta',
        'prefijo' => 'VEN',
        'consecutivo_desde' => 1,
        'consecutivo_hasta' => 9999,
        'consecutivo_actual' => 0,
        'anno' => now()->year,
        'resolucion_numero' => 'TEST-00001',
    ]);

    $departamento = Departamento::create(['nombre' => 'D1']);
    $ciudad = Ciudad::create(['nombre' => 'C1', 'departamento_id' => $departamento->id]);

    $bodega = Bodega::create([
        'nombre' => 'B1',
        'direccion1' => 'Calle 123',
        'departamento_id' => $departamento->id,
        'ciudad_id' => $ciudad->id,
        'activo' => true,
    ]);
    $producto = Producto::create([
        'nombre' => 'Artículo',
        'codigo' => 'P001',
        'precio_venta' => 100,
        'activo' => true,
        'exige_lote' => true,
    ]);

    $stock = StockBodega::create([
        'producto_id' => $producto->id,
        'bodega_id' => $bodega->id,
        'cantidad' => 0,
    ]);
    StockBodegaLote::create([
        'stock_bodega_id' => $stock->id,
        'lote' => 'XYZ',
        'cantidad' => 1,
    ]);

    $cliente = Cliente::forceCreate([
        'nombre' => 'Cliente 1',
        'documento' => '000',
        'telefono' => '000000',
        'email' => 'test@example.com',
        'direccion1' => 'Calle 1',
        'departamento_id' => $departamento->id,
        'ciudad_id' => $ciudad->id,
    ]);

    $venta = Venta::create([
        'cliente_id' => $cliente->id,
        'bodega_id' => $bodega->id,
        'subtotal' => 10,
        'descuento' => 0,
        'impuestos' => 0,
        'total' => 10,
        'saldo_pendiente' => 10,
        'estado_pago' => 'pendiente',
        'estado' => VentaEstado::BORRADOR,
    ]);

    DetalleVenta::create([
        'venta_id' => $venta->id,
        'producto_id' => $producto->id,
        'cantidad' => 2,
        'precio_unitario' => 5,
        'subtotal' => 10,
        'lote' => 'XYZ',
    ]);

    expect(fn () => VentaService::confirmar($venta))
        ->toThrow(InvalidArgumentException::class);
});
