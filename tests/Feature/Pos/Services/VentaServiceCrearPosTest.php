<?php

declare(strict_types=1);

use App\Enums\CajaEstado;
use App\Enums\EstadoPagoEnum;
use App\Enums\NumeracionEstado;
use App\Enums\TurnoEstado;
use App\Enums\UserEstado;
use App\Enums\VentaEstado;
use App\Models\AuditoriaDocumento;
use App\Models\Bodega;
use App\Models\Caja;
use App\Models\Ciudad;
use App\Models\Cliente;
use App\Models\Departamento;
use App\Models\FormaPago;
use App\Models\Impuesto;
use App\Models\MovimientoCaja;
use App\Models\MovimientoInventario;
use App\Models\Numeracion;
use App\Models\PagoCliente;
use App\Models\Producto;
use App\Models\StockBodega;
use App\Models\User;
use App\Models\Venta;
use App\Services\TurnoService;
use App\Services\VentaService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    if (Role::count() === 0) {
        (new RoleSeeder)->run();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    $this->user = User::factory()->create(['estado' => UserEstado::ACTIVO]);
    $this->user->assignRole('vendedor');

    $this->impuesto = Impuesto::create([
        'nombre' => 'IVA 19%',
        'porcentaje' => 19,
        'activo' => true,
    ]);

    $depto = Departamento::create(['nombre' => 'Depto POS']);
    $ciudad = Ciudad::create(['nombre' => 'Cúcuta', 'departamento_id' => $depto->id]);
    $this->bodega = Bodega::create([
        'nombre' => 'Bodega POS',
        'direccion1' => 'Calle 1',
        'departamento_id' => $depto->id,
        'ciudad_id' => $ciudad->id,
        'activo' => true,
    ]);
    $this->caja = Caja::create([
        'nombre' => 'Caja POS',
        'saldo_inicial' => 0,
        'activo' => true,
        'estado' => CajaEstado::ACTIVA,
        'usuario_id' => $this->user->id,
    ]);
    $this->cliente = Cliente::create([
        'nombre' => 'Cliente POS',
        'documento' => 'POS-'.uniqid(),
        'tipo_documento' => 'CC',
        'telefono' => '123',
        'email' => 'cliente-'.uniqid().'@test.com',
        'direccion1' => '-',
        'departamento_id' => $depto->id,
        'ciudad_id' => $ciudad->id,
    ]);
    $this->producto = Producto::create([
        'codigo' => 'POS-'.uniqid(),
        'nombre' => 'Producto POS',
        'precio_venta' => 1000,
        'precio_compra' => 500,
        'impuesto_id' => $this->impuesto->id,
        'activo' => true,
    ]);
    StockBodega::create([
        'producto_id' => $this->producto->id,
        'bodega_id' => $this->bodega->id,
        'cantidad' => 100,
    ]);

    Numeracion::create([
        'tipo_documento' => 'venta',
        'resolucion_numero' => 'POS-00001',
        'prefijo' => 'POS',
        'consecutivo_desde' => 1,
        'consecutivo_hasta' => 9999,
        'consecutivo_actual' => 0,
        'anno' => now()->year,
        'estado' => NumeracionEstado::ACTIVO,
    ]);

    $this->formaEfectivo = FormaPago::create([
        'nombre' => 'Efectivo',
        'activo' => true,
        'requiere_banco' => false,
    ]);

    $this->turno = TurnoService::abrir($this->user, [
        'caja_id' => $this->caja->id,
        'bodega_id' => $this->bodega->id,
        'saldo_inicial' => 0,
    ]);
});

it('VentaService::crearPos crea una venta con IVA desglosado y descuento de stock', function () {
    $resultado = VentaService::crearPos($this->user, [
        'cliente_id' => $this->cliente->id,
    ], [
        ['producto_id' => $this->producto->id, 'cantidad' => 2, 'descuento_unitario' => 0],
    ]);

    expect($resultado['id'])->toBeInt();
    expect($resultado['numero'])->toBe('POS000001');
    expect($resultado['total'])->toBe(2380.0);

    $venta = Venta::find($resultado['id']);
    expect($venta->estado)->toBe(VentaEstado::CONFIRMADA);
    expect((float) $venta->subtotal)->toBe(2000.0);
    expect((float) $venta->impuestos)->toBe(380.0);
    expect((float) $venta->total)->toBe(2380.0);

    $stock = StockBodega::where('producto_id', $this->producto->id)
        ->where('bodega_id', $this->bodega->id)->value('cantidad');
    expect((float) $stock)->toBe(98.0);
});

it('VentaService::crearPos registra movimiento de inventario y kardex', function () {
    $resultado = VentaService::crearPos($this->user, [
        'cliente_id' => $this->cliente->id,
    ], [
        ['producto_id' => $this->producto->id, 'cantidad' => 1],
    ]);

    $mov = MovimientoInventario::where('documento_tipo', 'venta')
        ->where('documento_id', $resultado['id'])->first();
    expect($mov)->not->toBeNull();
    expect($mov->tipo_movimiento->value)->toBe('salida_venta');
    expect((float) $mov->cantidad)->toBe(1.0);
    expect((float) $mov->stock_resultante)->toBe(99.0);
});

it('VentaService::crearPos crea PagoCliente y MovimientoCaja al recibir pagos', function () {
    $resultado = VentaService::crearPos($this->user, [
        'cliente_id' => $this->cliente->id,
        'pagos' => [
            ['forma_pago_id' => $this->formaEfectivo->id, 'monto' => 2380],
        ],
    ], [
        ['producto_id' => $this->producto->id, 'cantidad' => 2],
    ]);

    $venta = Venta::find($resultado['id']);
    expect($venta->estado)->toBe(VentaEstado::PAGADA);
    expect($venta->estado_pago)->toBe(EstadoPagoEnum::PAGADO);
    expect((float) $venta->saldo_pendiente)->toBe(0.0);

    expect(PagoCliente::where('cliente_id', $this->cliente->id)->count())->toBe(1);

    $mc = MovimientoCaja::where('caja_id', $this->caja->id)
        ->where('tipo', 'ingreso')->first();
    expect($mc)->not->toBeNull();
    expect((float) $mc->monto)->toBe(2380.0);
    expect($mc->forma_pago_id)->toBe($this->formaEfectivo->id);
});

it('VentaService::crearPos permite sobrepago en efectivo (vuelto solo visual)', function () {
    // Total: 2380; paga 5000 en efectivo → caja recibe 2380 (vuelto es UI-only)
    $resultado = VentaService::crearPos($this->user, [
        'cliente_id' => $this->cliente->id,
        'pagos' => [
            ['forma_pago_id' => $this->formaEfectivo->id, 'monto' => 5000],
        ],
    ], [
        ['producto_id' => $this->producto->id, 'cantidad' => 2],
    ]);

    $mc = MovimientoCaja::where('caja_id', $this->caja->id)
        ->where('tipo', 'ingreso')->first();
    expect((float) $mc->monto)->toBe(2380.0);
});

it('VentaService::crearPos rechaza pago mixto con no-efectivo excedente', function () {
    $formaTransferencia = FormaPago::create([
        'nombre' => 'Transferencia',
        'activo' => true,
        'requiere_banco' => true,
    ]);

    // Total 2380; paga 1000 efectivo + 2000 transferencia = 3000, no-efectivo excede
    expect(fn () => VentaService::crearPos($this->user, [
        'cliente_id' => $this->cliente->id,
        'pagos' => [
            ['forma_pago_id' => $this->formaEfectivo->id, 'monto' => 1000],
            ['forma_pago_id' => $formaTransferencia->id, 'monto' => 2000],
        ],
    ], [
        ['producto_id' => $this->producto->id, 'cantidad' => 2],
    ]))->toThrow(InvalidArgumentException::class, 'Pago mixto inválido');
});

it('VentaService::crearPos acepta pago mixto exacto', function () {
    $formaTransferencia = FormaPago::create([
        'nombre' => 'Transferencia',
        'activo' => true,
        'requiere_banco' => true,
    ]);

    $resultado = VentaService::crearPos($this->user, [
        'cliente_id' => $this->cliente->id,
        'pagos' => [
            ['forma_pago_id' => $this->formaEfectivo->id, 'monto' => 1000],
            ['forma_pago_id' => $formaTransferencia->id, 'monto' => 1380],
        ],
    ], [
        ['producto_id' => $this->producto->id, 'cantidad' => 2],
    ]);

    expect($resultado['total'])->toBe(2380.0);
    expect(PagoCliente::where('cliente_id', $this->cliente->id)->count())->toBe(2);
});

it('VentaService::crearPos rechaza si no hay turno POS abierto', function () {
    $this->turno->update(['estado' => TurnoEstado::CERRADO, 'fecha_cierre' => now()]);

    expect(fn () => VentaService::crearPos($this->user, [
        'cliente_id' => $this->cliente->id,
    ], [
        ['producto_id' => $this->producto->id, 'cantidad' => 1],
    ]))->toThrow(InvalidArgumentException::class, 'turno POS abierto');
});

it('VentaService::crearPos rechaza stock insuficiente', function () {
    expect(fn () => VentaService::crearPos($this->user, [
        'cliente_id' => $this->cliente->id,
    ], [
        ['producto_id' => $this->producto->id, 'cantidad' => 200],
    ]))->toThrow(InvalidArgumentException::class, 'Stock insuficiente');
});

it('VentaService::crearPos falla si no hay numeración activa', function () {
    Numeracion::where('tipo_documento', 'venta')->update(['estado' => NumeracionEstado::INACTIVO]);

    expect(fn () => VentaService::crearPos($this->user, [
        'cliente_id' => $this->cliente->id,
    ], [
        ['producto_id' => $this->producto->id, 'cantidad' => 1],
    ]))->toThrow(Exception::class);
});

it('VentaService::crearPos registra auditoría con accion=pos.crear', function () {
    $resultado = VentaService::crearPos($this->user, [
        'cliente_id' => $this->cliente->id,
    ], [
        ['producto_id' => $this->producto->id, 'cantidad' => 1],
    ]);

    $aud = AuditoriaDocumento::where('documento_tipo', 'venta')
        ->where('documento_id', $resultado['id'])
        ->where('accion', 'pos.crear')->first();
    expect($aud)->not->toBeNull();
});
