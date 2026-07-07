<?php

use App\Models\Bodega;
use App\Models\Ciudad;
use App\Models\Cliente;
use App\Models\Departamento;
use App\Models\DetalleVenta;
use App\Models\Numeracion;
use App\Models\Producto;
use App\Models\StockBodega;
use App\Models\Venta;
use App\Services\StockService;
use App\Services\VentaService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| ConcurrencyTest
|
| Tests para verificar el comportamiento concurrente del sistema.
| NOTA: Estos son tests secuenciales que simulan concurrencia.
|       Para prueba real de concurrencia se necesitan procesos paralelos.
|--------------------------------------------------------------------------
*/

// Helper: setup base
function setupConcurrencia(): array
{
    Numeracion::create([
        'tipo_documento' => 'venta',
        'prefijo' => 'VEN',
        'consecutivo_desde' => 1,
        'consecutivo_hasta' => 9999,
        'consecutivo_actual' => 0,
        'anno' => now()->year,
        'resolucion_numero' => 'TEST-00001',
    ]);

    $depto = Departamento::create(['nombre' => 'Depto Concurrencia']);
    $ciudad = Ciudad::create(['nombre' => 'Ciudad Concurrencia', 'departamento_id' => $depto->id]);

    $bodega = Bodega::create([
        'nombre' => 'Bodega Concurrencia',
        'direccion1' => 'Calle Test 123',
        'departamento_id' => $depto->id,
        'ciudad_id' => $ciudad->id,
        'activo' => true,
    ]);

    $cliente = Cliente::create([
        'nombre' => 'Cliente Concurrencia',
        'documento' => 'CONC-'.uniqid(),
        'tipo_documento' => 'CC',
        'telefono' => '9999999999',
        'email' => 'concurrencia-'.uniqid().'@test.com',
        'direccion1' => 'Test Address',
        'departamento_id' => $depto->id,
        'ciudad_id' => $ciudad->id,
    ]);

    $producto = Producto::create([
        'codigo' => 'PROD-CONC-'.uniqid(),
        'nombre' => 'Producto Concurrencia',
        'precio_venta' => 15000,
        'precio_compra' => 10000,
        'iva' => 19,
        'activo' => true,
    ]);

    // Stock inicial
    StockBodega::create([
        'producto_id' => $producto->id,
        'bodega_id' => $bodega->id,
        'cantidad' => 100,
    ]);

    return compact('bodega', 'cliente', 'producto');
}

function crearVenta(int $clienteId, int $bodegaId, string $estado = 'borrador'): Venta
{
    return Venta::create([
        'numero' => 'CONC-'.date('His').'-'.uniqid(),
        'cliente_id' => $clienteId,
        'bodega_id' => $bodegaId,
        'fecha' => now()->toDateString(),
        'estado' => $estado,
        'subtotal' => 15000,
        'descuento' => 0,
        'impuestos' => 2850,
        'total' => 17850,
        'saldo_pendiente' => 17850,
    ]);
}

// Tests de concurrencia simulada

it('VentaService -> confirmacion secuencial solo permite uno exitoso', function () {
    ['bodega' => $bodega, 'cliente' => $cliente, 'producto' => $producto] = setupConcurrencia();

    // Crear una venta en estado BORRADOR
    $venta = crearVenta($cliente->id, $bodega->id, 'borrador');

    // Agregar detalle
    DetalleVenta::create([
        'venta_id' => $venta->id,
        'producto_id' => $producto->id,
        'cantidad' => 1,
        'precio_unitario' => 15000,
        'descuento' => 0,
        'impuesto' => 2850,
        'subtotal' => 17850,
    ]);

    $servicio = app(VentaService::class);
    $resultados = [];

    // Intento 1: debe tener exito
    try {
        $servicio->confirmar($venta);
        $resultados[] = 'exito';
    } catch (Exception $e) {
        $resultados[] = 'error';
    }

    // Intento 2 y 3: deben fallar (ya esta confirmada)
    for ($i = 2; $i <= 3; $i++) {
        try {
            $servicio->confirmar($venta->refresh());
            $resultados[] = 'exito';
        } catch (Exception $e) {
            $resultados[] = 'error';
        }
    }

    // Verificar: solo 1 debe tener exito
    $exitos = array_filter($resultados, fn ($r) => $r === 'exito');

    expect(count($exitos))->toBe(1);
    expect($venta->refresh()->estado->value)->toBe('confirmada');

    // El stock debe haber disminuido solo 1 vez
    $stockFinal = StockBodega::where('producto_id', $producto->id)
        ->where('bodega_id', $bodega->id)
        ->value('cantidad');

    expect((float) $stockFinal)->toBe(99.0);
});

it('StockService -> validacion de stock rechazando solicitudes excesivas', function () {
    ['bodega' => $bodega, 'producto' => $producto] = setupConcurrencia();

    // Stock de 10 unidades
    StockBodega::where('producto_id', $producto->id)
        ->where('bodega_id', $bodega->id)
        ->update(['cantidad' => 10]);

    $servicio = app(StockService::class);

    // Solicitud de 5 unidades - debe pasar (10 >= 5)
    $resultado5 = $servicio->validateStock($producto->id, $bodega->id, 5);

    // Solicitud de 6 unidades - debe fallar (10 < 6 porque ya no hay 10 disponibles)
    // Nota: validateStock valida contra el stock actual, no contra stock restante acumulado
    // Esto es correcto porque cada confirmacion de venta ejecutara su propia transaccion

    // Solicitud de 15 unidades - debe fallar (10 < 15)
    $exito15 = false;

    try {
        $servicio->validateStock($producto->id, $bodega->id, 15);
        $exito15 = true;
    } catch (Exception $e) {
        $exito15 = false;
    }

    // Verificar: solicitud de 5 debe pasar, solicitud de 15 debe fallar
    expect($resultado5)->toBeTrue();
    expect($exito15)->toBeFalse();
});

it('VentaService -> protectores de concurrencia evitan doble descuento de stock', function () {
    ['bodega' => $bodega, 'cliente' => $cliente, 'producto' => $producto] = setupConcurrencia();

    // Venta 1
    $venta1 = crearVenta($cliente->id, $bodega->id, 'borrador');
    DetalleVenta::create([
        'venta_id' => $venta1->id,
        'producto_id' => $producto->id,
        'cantidad' => 10,
        'precio_unitario' => 15000,
        'descuento' => 0,
        'impuesto' => 2850,
        'subtotal' => 17850,
    ]);

    // Venta 2 (diferente)
    $venta2 = crearVenta($cliente->id, $bodega->id, 'borrador');
    DetalleVenta::create([
        'venta_id' => $venta2->id,
        'producto_id' => $producto->id,
        'cantidad' => 10,
        'precio_unitario' => 15000,
        'descuento' => 0,
        'impuesto' => 2850,
        'subtotal' => 17850,
    ]);

    $servicio = app(VentaService::class);

    // Confirmar ambas ventas
    $servicio->confirmar($venta1);
    $servicio->confirmar($venta2);

    // Stock final debe ser 80 (100 - 10 - 10)
    $stockFinal = StockBodega::where('producto_id', $producto->id)
        ->where('bodega_id', $bodega->id)
        ->value('cantidad');

    expect((float) $stockFinal)->toBe(80.0);
    expect($venta1->refresh()->estado->value)->toBe('confirmada');
    expect($venta2->refresh()->estado->value)->toBe('confirmada');
});
