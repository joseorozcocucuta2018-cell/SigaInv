<?php

use App\Enums\VentaEstado;
use App\Models\Bodega;
use App\Models\Ciudad;
use App\Models\Cliente;
use App\Models\Departamento;
use App\Models\DetalleVenta;
use App\Models\Numeracion;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| VentaCostoUnitarioTest
|
| Verifica que el detalle de venta guarda correctamente el costo_unitario.
| No usa factories — crea todos los modelos directamente.
|--------------------------------------------------------------------------
*/

function setupVentaCosto(): array
{
    Numeracion::create([
        'tipo_documento' => 'venta',
        'resolucion_numero' => 'TEST-00001',
        'prefijo' => 'VEN',
        'consecutivo_desde' => 1,
        'consecutivo_hasta' => 9999,
        'consecutivo_actual' => 0,
        'anno' => now()->year,
    ]);

    $depto = Departamento::create(['nombre' => 'Depto Venta']);
    $ciudad = Ciudad::create(['nombre' => 'Ciudad Venta', 'departamento_id' => $depto->id]);

    $bodega = Bodega::create([
        'nombre' => 'Bodega Venta',
        'direccion1' => 'Calle 1',
        'departamento_id' => $depto->id,
        'ciudad_id' => $ciudad->id,
    ]);

    $cliente = Cliente::create([
        'nombre' => 'Cliente Venta Test',
        'documento' => 'VNT-'.uniqid(),
        'tipo_documento' => 'CC',
        'telefono' => '123',
        'email' => 'venta-'.uniqid().'@test.com',
        'direccion1' => 'Calle 1',
        'departamento_id' => $depto->id,
        'ciudad_id' => $ciudad->id,
    ]);

    $producto = Producto::create([
        'codigo' => 'VNT-'.uniqid(),
        'nombre' => 'Producto Venta',
        'precio_venta' => 150,
        'precio_compra' => 100,
        'activo' => true,
    ]);

    $venta = Venta::create([
        'numero' => 'VEN-'.uniqid(),
        'estado' => VentaEstado::BORRADOR,
        'cliente_id' => $cliente->id,
        'bodega_id' => $bodega->id,
        'subtotal' => 100,
        'descuento' => 0,
        'impuestos' => 0,
        'total' => 100,
        'saldo_pendiente' => 100,
        'estado_pago' => 'pendiente',
    ]);

    return compact('venta', 'producto');
}

it('detalle venta guarda costo_unitario correctamente', function () {
    ['venta' => $venta, 'producto' => $producto] = setupVentaCosto();

    $detalle = DetalleVenta::create([
        'venta_id' => $venta->id,
        'producto_id' => $producto->id,
        'cantidad' => 1,
        'precio_unitario' => 150,
        'costo_unitario' => 100,
    ]);

    expect((float) $detalle->costo_unitario)->toBe(100.0);
});

it('detalle venta sin costo_unitario guarda null', function () {
    ['venta' => $venta, 'producto' => $producto] = setupVentaCosto();

    $detalle = DetalleVenta::create([
        'venta_id' => $venta->id,
        'producto_id' => $producto->id,
        'cantidad' => 1,
        'precio_unitario' => 150,
        'costo_unitario' => null,
    ]);

    expect($detalle->costo_unitario)->toBeNull();
});

it('detalle venta persiste costo_unitario correctamente en BD', function () {
    ['venta' => $venta, 'producto' => $producto] = setupVentaCosto();

    $detalle = DetalleVenta::create([
        'venta_id' => $venta->id,
        'producto_id' => $producto->id,
        'cantidad' => 2,
        'precio_unitario' => 75,
        'costo_unitario' => 50,
    ]);

    // Recargar desde BD para verificar persistencia
    $detalleFresh = DetalleVenta::find($detalle->id);
    expect((float) $detalleFresh->costo_unitario)->toBe(50.0);
});
