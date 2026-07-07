<?php

declare(strict_types=1);

use App\Enums\CajaEstado;
use App\Enums\ClienteEstado;
use App\Enums\EstadoPagoEnum;
use App\Enums\NumeracionEstado;
use App\Enums\RemisionEstado;
use App\Enums\TurnoEstado;
use App\Enums\UserEstado;
use App\Models\AuditoriaDocumento;
use App\Models\Bodega;
use App\Models\Caja;
use App\Models\Ciudad;
use App\Models\Cliente;
use App\Models\Departamento;
use App\Models\DetalleRemision;
use App\Models\FormaPago;
use App\Models\Impuesto;
use App\Models\MovimientoCaja;
use App\Models\MovimientoInventario;
use App\Models\Numeracion;
use App\Models\PagoCliente;
use App\Models\Producto;
use App\Models\Remision;
use App\Models\StockBodega;
use App\Models\User;
use App\Services\RemisionService;
use App\Services\TurnoService;
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
        'estado' => ClienteEstado::ACTIVO,
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
        'tipo_documento' => 'remision',
        'resolucion_numero' => 'POS-00001',
        'prefijo' => 'REM',
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

it('RemisionService::crearPos crea una remisión sin IVA con descuento de stock', function () {
    $resultado = RemisionService::crearPos($this->user, [
        'cliente_id' => $this->cliente->id,
    ], [
        ['producto_id' => $this->producto->id, 'cantidad' => 2, 'descuento_unitario' => 0],
    ]);

    expect($resultado['id'])->toBeInt();
    expect($resultado['numero'])->toBe('REM000001');
    expect($resultado['total'])->toBe(2000.0);

    $remision = Remision::find($resultado['id']);
    expect($remision->estado)->toBe(RemisionEstado::CONFIRMADA);
    expect((float) $remision->subtotal)->toBe(2000.0);
    expect((float) $remision->impuestos)->toBe(0.0);
    expect((float) $remision->total)->toBe(2000.0);

    $stock = StockBodega::where('producto_id', $this->producto->id)
        ->where('bodega_id', $this->bodega->id)->value('cantidad');
    expect((float) $stock)->toBe(98.0);
});

it('RemisionService::crearPos crea remisión a crédito cuando no se reciben pagos', function () {
    $resultado = RemisionService::crearPos($this->user, [
        'cliente_id' => $this->cliente->id,
    ], [
        ['producto_id' => $this->producto->id, 'cantidad' => 1],
    ]);

    $remision = Remision::find($resultado['id']);
    expect($remision->estado_pago)->toBe(EstadoPagoEnum::PENDIENTE);
    expect((float) $remision->saldo_pendiente)->toBe(1000.0);
    expect(PagoCliente::where('cliente_id', $this->cliente->id)->count())->toBe(0);
});

it('RemisionService::crearPos crea PagoCliente y MovimientoCaja en contado', function () {
    $resultado = RemisionService::crearPos($this->user, [
        'cliente_id' => $this->cliente->id,
        'pagos' => [
            ['forma_pago_id' => $this->formaEfectivo->id, 'monto' => 2000],
        ],
    ], [
        ['producto_id' => $this->producto->id, 'cantidad' => 2],
    ]);

    $remision = Remision::find($resultado['id']);
    expect($remision->estado)->toBe(RemisionEstado::CONFIRMADA);
    expect($remision->estado_pago)->toBe(EstadoPagoEnum::PAGADO);
    expect((float) $remision->saldo_pendiente)->toBe(0.0);

    expect(PagoCliente::where('cliente_id', $this->cliente->id)->count())->toBe(1);

    $mc = MovimientoCaja::where('caja_id', $this->caja->id)
        ->where('tipo', 'ingreso')->first();
    expect($mc)->not->toBeNull();
    expect((float) $mc->monto)->toBe(2000.0);
});

it('RemisionService::crearPos descuenta stock y registra movimiento de inventario', function () {
    $resultado = RemisionService::crearPos($this->user, [
        'cliente_id' => $this->cliente->id,
    ], [
        ['producto_id' => $this->producto->id, 'cantidad' => 3],
    ]);

    $mov = MovimientoInventario::where('documento_tipo', 'remision')
        ->where('documento_id', $resultado['id'])->first();
    expect($mov)->not->toBeNull();
    expect($mov->tipo_movimiento->value)->toBe('salida_remision');
    expect((float) $mov->cantidad)->toBe(3.0);

    expect(DetalleRemision::where('remision_id', $resultado['id'])->count())->toBe(1);
});

it('RemisionService::crearPos permite sobrepago en efectivo (vuelto solo visual)', function () {
    RemisionService::crearPos($this->user, [
        'cliente_id' => $this->cliente->id,
        'pagos' => [
            ['forma_pago_id' => $this->formaEfectivo->id, 'monto' => 5000],
        ],
    ], [
        ['producto_id' => $this->producto->id, 'cantidad' => 2],
    ]);

    $mc = MovimientoCaja::where('caja_id', $this->caja->id)
        ->where('tipo', 'ingreso')->first();
    expect((float) $mc->monto)->toBe(2000.0);
});

it('RemisionService::crearPos rechaza pago mixto con no-efectivo excedente', function () {
    $formaTransferencia = FormaPago::create([
        'nombre' => 'Transferencia',
        'activo' => true,
        'requiere_banco' => true,
    ]);

    expect(fn () => RemisionService::crearPos($this->user, [
        'cliente_id' => $this->cliente->id,
        'pagos' => [
            ['forma_pago_id' => $this->formaEfectivo->id, 'monto' => 500],
            ['forma_pago_id' => $formaTransferencia->id, 'monto' => 2000],
        ],
    ], [
        ['producto_id' => $this->producto->id, 'cantidad' => 2],
    ]))->toThrow(InvalidArgumentException::class, 'Pago mixto inválido');
});

it('RemisionService::crearPos rechaza si no hay turno POS abierto', function () {
    $this->turno->update(['estado' => TurnoEstado::CERRADO, 'fecha_cierre' => now()]);

    expect(fn () => RemisionService::crearPos($this->user, [
        'cliente_id' => $this->cliente->id,
    ], [
        ['producto_id' => $this->producto->id, 'cantidad' => 1],
    ]))->toThrow(InvalidArgumentException::class, 'turno POS abierto');
});

it('RemisionService::crearPos rechaza stock insuficiente', function () {
    expect(fn () => RemisionService::crearPos($this->user, [
        'cliente_id' => $this->cliente->id,
    ], [
        ['producto_id' => $this->producto->id, 'cantidad' => 200],
    ]))->toThrow(InvalidArgumentException::class, 'Stock insuficiente');
});

it('RemisionService::crearPos falla si no hay numeración activa', function () {
    Numeracion::where('tipo_documento', 'remision')->update(['estado' => NumeracionEstado::INACTIVO]);

    expect(fn () => RemisionService::crearPos($this->user, [
        'cliente_id' => $this->cliente->id,
    ], [
        ['producto_id' => $this->producto->id, 'cantidad' => 1],
    ]))->toThrow(Exception::class);
});

it('RemisionService::crearPos registra auditoría con accion=pos.crear', function () {
    $resultado = RemisionService::crearPos($this->user, [
        'cliente_id' => $this->cliente->id,
    ], [
        ['producto_id' => $this->producto->id, 'cantidad' => 1],
    ]);

    $aud = AuditoriaDocumento::where('documento_tipo', 'remision')
        ->where('documento_id', $resultado['id'])
        ->where('accion', 'pos.crear')->first();
    expect($aud)->not->toBeNull();
});
