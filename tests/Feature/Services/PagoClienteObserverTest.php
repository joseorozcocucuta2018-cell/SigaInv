<?php

use App\Enums\EstadoPagoEnum;
use App\Enums\VentaEstado;
use App\Models\Bodega;
use App\Models\Ciudad;
use App\Models\Cliente;
use App\Models\Departamento;
use App\Models\DetallePagoCliente;
use App\Models\FormaPago;
use App\Models\Numeracion;
use App\Models\PagoCliente;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| PagoClienteObserverTest — Sistema Waterfall (cascada)
|
| Verifica la distribución automática de pagos desde el documento más antiguo:
| - Pago parcial a un solo documento
| - Pago completo a un solo documento
| - Distribución en cascada a múltiples documentos
| - Caso real: facturas $140K y $200K, pago de $300K
| - Eliminación de pago revierte la distribución
| - Sin documentos pendientes = sin detalles
|--------------------------------------------------------------------------
*/

// ── Helper: setup base ────────────────────────────────────────────────────────
function setupPagoClienteWaterfall(): array
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

    $depto = Departamento::create(['nombre' => 'Depto Test']);
    $ciudad = Ciudad::create(['nombre' => 'Ciudad Test', 'departamento_id' => $depto->id]);

    $bodega = Bodega::create([
        'nombre' => 'Bodega Test',
        'direccion1' => 'Calle 1',
        'departamento_id' => $depto->id,
        'ciudad_id' => $ciudad->id,
    ]);

    $cliente = Cliente::create([
        'nombre' => 'Cliente Test',
        'documento' => 'DOC-'.uniqid(),
        'tipo_documento' => 'CC',
        'telefono' => '123',
        'email' => 'test-'.uniqid().'@test.com',
        'direccion1' => 'Calle 1',
        'departamento_id' => $depto->id,
        'ciudad_id' => $ciudad->id,
    ]);

    $formaPago = FormaPago::create([
        'nombre' => 'Efectivo',
        'activo' => true,
    ]);

    return compact('cliente', 'bodega', 'formaPago');
}

function crearVentaPago(int $clienteId, int $bodegaId, float $total, ?string $fecha = null): Venta
{
    return Venta::create([
        'numero' => 'VEN-'.uniqid(),
        'estado' => VentaEstado::CONFIRMADA,
        'cliente_id' => $clienteId,
        'bodega_id' => $bodegaId,
        'subtotal' => $total,
        'descuento' => 0,
        'impuestos' => 0,
        'total' => $total,
        'saldo_pendiente' => $total,
        'estado_pago' => 'pendiente',
        'fecha' => $fecha ?? now(),
    ]);
}

function crearPagoWaterfall(int $clienteId, int $formaPagoId, float $monto): PagoCliente
{
    return PagoCliente::create([
        'numero' => 'PAG-'.uniqid(),
        'cliente_id' => $clienteId,
        'forma_pago_id' => $formaPagoId,
        'fecha' => now(),
        'monto' => $monto,
    ]);
}

// ── Distribución waterfall ───────────────────────────────────────────────────

it('Waterfall → pago parcial a un solo documento', function () {
    ['cliente' => $c, 'bodega' => $b, 'formaPago' => $fp] = setupPagoClienteWaterfall();

    $venta = crearVentaPago($c->id, $b->id, 1000);

    $pago = crearPagoWaterfall($c->id, $fp->id, 400);

    $venta->refresh();
    expect((float) $venta->saldo_pendiente)->toBe(600.0);
    expect($venta->estado_pago)->toBe(EstadoPagoEnum::PARCIAL);
    expect($pago->detalles)->toHaveCount(1);
    expect((float) $pago->detalles->first()->monto_aplicado)->toBe(400.0);
});

it('Waterfall → pago completo a un solo documento', function () {
    ['cliente' => $c, 'bodega' => $b, 'formaPago' => $fp] = setupPagoClienteWaterfall();

    $venta = crearVentaPago($c->id, $b->id, 1000);

    crearPagoWaterfall($c->id, $fp->id, 1000);

    $venta->refresh();
    expect((float) $venta->saldo_pendiente)->toBe(0.0);
    expect($venta->estado_pago)->toBe(EstadoPagoEnum::PAGADO);
});

it('Waterfall → distribución en cascada a múltiples documentos (más antiguo primero)', function () {
    ['cliente' => $c, 'bodega' => $b, 'formaPago' => $fp] = setupPagoClienteWaterfall();

    $venta1 = crearVentaPago($c->id, $b->id, 500, '2026-01-01');
    $venta2 = crearVentaPago($c->id, $b->id, 800, '2026-01-15');

    // Pago de 700: cubre toda la venta1 (500) + 200 de venta2
    $pago = crearPagoWaterfall($c->id, $fp->id, 700);

    $venta1->refresh();
    $venta2->refresh();

    expect((float) $venta1->saldo_pendiente)->toBe(0.0);
    expect($venta1->estado_pago)->toBe(EstadoPagoEnum::PAGADO);
    expect((float) $venta2->saldo_pendiente)->toBe(600.0);
    expect($venta2->estado_pago)->toBe(EstadoPagoEnum::PARCIAL);

    // Verificar detalles
    $detalles = $pago->detalles->sortBy('id');
    expect($detalles)->toHaveCount(2);
    expect((float) $detalles->first()->monto_aplicado)->toBe(500.0);
    expect((float) $detalles->last()->monto_aplicado)->toBe(200.0);
});

it('Waterfall → caso real: facturas $140K y $200K, pago de $300K', function () {
    ['cliente' => $c, 'bodega' => $b, 'formaPago' => $fp] = setupPagoClienteWaterfall();

    $venta1 = crearVentaPago($c->id, $b->id, 140000, '2026-02-01');
    $venta2 = crearVentaPago($c->id, $b->id, 200000, '2026-02-15');

    $pago = crearPagoWaterfall($c->id, $fp->id, 300000);

    $venta1->refresh();
    $venta2->refresh();

    // Primera factura queda pagada completamente
    expect((float) $venta1->saldo_pendiente)->toBe(0.0);
    expect($venta1->estado_pago)->toBe(EstadoPagoEnum::PAGADO);

    // Segunda factura queda con $40K pendiente
    expect((float) $venta2->saldo_pendiente)->toBe(40000.0);
    expect($venta2->estado_pago)->toBe(EstadoPagoEnum::PARCIAL);

    $detalles = $pago->detalles->sortBy('id');
    expect((float) $detalles->first()->monto_aplicado)->toBe(140000.0);
    expect((float) $detalles->last()->monto_aplicado)->toBe(160000.0);
});

it('Waterfall → múltiples pagos se acumulan correctamente', function () {
    ['cliente' => $c, 'bodega' => $b, 'formaPago' => $fp] = setupPagoClienteWaterfall();

    $venta = crearVentaPago($c->id, $b->id, 1000);

    crearPagoWaterfall($c->id, $fp->id, 300);
    crearPagoWaterfall($c->id, $fp->id, 300);
    crearPagoWaterfall($c->id, $fp->id, 400);

    $venta->refresh();
    expect((float) $venta->saldo_pendiente)->toBe(0.0);
    expect($venta->estado_pago)->toBe(EstadoPagoEnum::PAGADO);
});

it('Waterfall → pago mayor al saldo no deja saldo negativo', function () {
    ['cliente' => $c, 'bodega' => $b, 'formaPago' => $fp] = setupPagoClienteWaterfall();

    $venta = crearVentaPago($c->id, $b->id, 1000);

    $pago = crearPagoWaterfall($c->id, $fp->id, 1500);

    $venta->refresh();
    expect((float) $venta->saldo_pendiente)->toBe(0.0);
    expect($venta->estado_pago)->toBe(EstadoPagoEnum::PAGADO);

    // Solo se aplicaron 1000 al detalle (el saldo real del documento)
    expect((float) $pago->detalles->first()->monto_aplicado)->toBe(1000.0);
});

// ── Eliminación — reversión ──────────────────────────────────────────────────

it('Waterfall → eliminar pago revierte la distribución completa', function () {
    ['cliente' => $c, 'bodega' => $b, 'formaPago' => $fp] = setupPagoClienteWaterfall();

    $venta1 = crearVentaPago($c->id, $b->id, 500, '2026-01-01');
    $venta2 = crearVentaPago($c->id, $b->id, 800, '2026-01-15');

    $pago = crearPagoWaterfall($c->id, $fp->id, 700);

    // Verificar estado post-pago
    expect((float) $venta1->refresh()->saldo_pendiente)->toBe(0.0);
    expect((float) $venta2->refresh()->saldo_pendiente)->toBe(600.0);

    // Eliminar pago
    $pago->delete();

    $venta1->refresh();
    $venta2->refresh();

    expect((float) $venta1->saldo_pendiente)->toBe(500.0);
    expect($venta1->estado_pago)->toBe(EstadoPagoEnum::PENDIENTE);
    expect((float) $venta2->saldo_pendiente)->toBe(800.0);
    expect($venta2->estado_pago)->toBe(EstadoPagoEnum::PENDIENTE);

    // Detalles eliminados
    expect(DetallePagoCliente::where('pago_cliente_id', $pago->id)->count())->toBe(0);
});

it('Waterfall → eliminar pago parcial restaura estado correcto', function () {
    ['cliente' => $c, 'bodega' => $b, 'formaPago' => $fp] = setupPagoClienteWaterfall();

    $venta = crearVentaPago($c->id, $b->id, 1000);

    $pago1 = crearPagoWaterfall($c->id, $fp->id, 300);
    $pago2 = crearPagoWaterfall($c->id, $fp->id, 300);

    expect((float) $venta->refresh()->saldo_pendiente)->toBe(400.0);

    // Eliminar segundo pago
    $pago2->delete();

    $venta->refresh();
    expect((float) $venta->saldo_pendiente)->toBe(700.0);
    expect($venta->estado_pago)->toBe(EstadoPagoEnum::PARCIAL);
});

// ── Sin documentos pendientes ────────────────────────────────────────────────

it('Waterfall → pago sin documentos pendientes no genera detalles', function () {
    ['cliente' => $c, 'formaPago' => $fp] = setupPagoClienteWaterfall();

    $pago = crearPagoWaterfall($c->id, $fp->id, 500);

    expect($pago->detalles)->toHaveCount(0);
});
